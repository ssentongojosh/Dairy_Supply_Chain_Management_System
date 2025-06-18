package com.dscms.java_server.Services;

import org.apache.pdfbox.pdmodel.PDDocument;
import org.opencv.calib3d.Calib3d;
import org.opencv.core.*;
import org.opencv.features2d.BFMatcher;
import org.opencv.features2d.ORB;
import org.opencv.imgcodecs.Imgcodecs;
import org.opencv.imgproc.Imgproc;
import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;

import java.io.File;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

@Service
public class UrsbCertificateService {
    public boolean isVerified(MultipartFile ursbCertificate) {
      PDDocument pdf = DocumentVerification.loadpdf(ursbCertificate);

      try {
        File file = File.createTempFile("doc","pdf");

        for (int i=0; i< pdf.getNumberOfPages(); i++){
          file = DocumentVerification.pdfToImage(pdf, i);
        }

        Mat certificate = Imgcodecs.imread(file.getAbsolutePath());

        return isLogoMatched(certificate);

      } catch (IOException e) {
        throw new RuntimeException(e){
        };
      }


    }

  public static boolean isLogoMatched(Mat sceneImage) {
    // Load the logo template
    Mat logoImage = Imgcodecs.imread("C:/Users/DELL/OneDrive/Desktop/URSB/URSB-LOGO.jpg", Imgcodecs.IMREAD_GRAYSCALE);
    if (logoImage.empty()) {
      System.out.println("❌ Could not load logo image");

      return false;
    }

    // Convert scene to grayscale if not already
    if (sceneImage.channels() > 1) {
      Imgproc.cvtColor(sceneImage, sceneImage, Imgproc.COLOR_BGR2GRAY);
    }

    // Initialize ORB
    ORB orb = ORB.create();

    // Detect keypoints and descriptors
    MatOfKeyPoint keypointsLogo = new MatOfKeyPoint();
    MatOfKeyPoint keypointsScene = new MatOfKeyPoint();
    Mat descriptorsLogo = new Mat();
    Mat descriptorsScene = new Mat();

    orb.detectAndCompute(logoImage, new Mat(), keypointsLogo, descriptorsLogo);
    orb.detectAndCompute(sceneImage, new Mat(), keypointsScene, descriptorsScene);

    if (descriptorsLogo.empty() || descriptorsScene.empty()) {
      System.out.println("❌ Descriptors missing.");
      return false;
    }

    // Match descriptors using BruteForce-Hamming
    BFMatcher matcher = BFMatcher.create(Core.NORM_HAMMING, true);
    MatOfDMatch matches = new MatOfDMatch();
    matcher.match(descriptorsLogo, descriptorsScene, matches);

    // Filter good matches
    List<DMatch> matchList = matches.toList();
    double minDist = Double.MAX_VALUE;
    for (DMatch m : matchList) {
      if (m.distance < minDist) minDist = m.distance;
    }

    List<DMatch> goodMatches = new ArrayList<>();
    for (DMatch m : matchList) {
      if (m.distance <= Math.max(2 * minDist, 30.0)) {
        goodMatches.add(m);
      }
    }

    // Check if we have enough good matches
    if (goodMatches.size() < 15) {
      System.out.println("❌ Too few good matches: " + goodMatches.size());
      return false;
    }

    // Try to compute homography to verify spatial alignment
    List<Point> objPoints = new ArrayList<>();
    List<Point> scenePoints = new ArrayList<>();

    List<KeyPoint> kpLogo = keypointsLogo.toList();
    List<KeyPoint> kpScene = keypointsScene.toList();

    for (DMatch m : goodMatches) {
      objPoints.add(kpLogo.get(m.queryIdx).pt);
      scenePoints.add(kpScene.get(m.trainIdx).pt);
    }

    MatOfPoint2f objMat = new MatOfPoint2f();
    objMat.fromList(objPoints);
    MatOfPoint2f sceneMat = new MatOfPoint2f();
    sceneMat.fromList(scenePoints);

    Mat H = Calib3d.findHomography(objMat, sceneMat, Calib3d.RANSAC, 5);
    if (H.empty()) {
      System.out.println("❌ Homography could not be computed.");
      return false;
    }

    System.out.println("✅ Logo detected with " + goodMatches.size() + " good matches.");
    return true;
  }
}
