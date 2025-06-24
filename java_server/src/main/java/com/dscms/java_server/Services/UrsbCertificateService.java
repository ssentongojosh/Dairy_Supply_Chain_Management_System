package com.dscms.java_server.Services;

import org.apache.pdfbox.pdmodel.PDDocument;
import org.opencv.core.*;
import org.opencv.features2d.BFMatcher;
import org.opencv.imgcodecs.Imgcodecs;
import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;
import org.opencv.features2d.*;

import java.io.File;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.regex.Pattern;

@Service
public class UrsbCertificateService {

    private final DocumentVerification documentVerification;

    public UrsbCertificateService(DocumentVerification documentVerification){
      this.documentVerification = documentVerification;
    }



  public boolean isVerified(MultipartFile ursbCertificate) {
    System.out.println("Verifying URSB Certificate...\n");

      //Load the pdf
      File file = DocumentVerification.loadpdf(ursbCertificate);

      // Load the logo template
          //Mat ursbLogo = Imgcodecs.imread("C:/Users/DELL/OneDrive/Desktop/URSB/URSB-LOGO2.jpg", Imgcodecs.IMREAD_GRAYSCALE);
      Mat coatOfArmsLogo = Imgcodecs.imread("C:/xampp/htdocs/Dairy_Supply_Chain_Management_System/java_server/src/main/resources/Images/COA.jpeg", Imgcodecs.IMREAD_GRAYSCALE);

      //Extract the text from the certificate
      String text = documentVerification.extractText(file);
      //System.out.println(text);

      //Generate a pattern to check if the certificate contains "Certificate Of Incorporation on it"
      final Pattern CERTIFICATE_PATTERN = Pattern.compile("Certificate\\s+of\\s+[HIli1|]ncorporation", Pattern.CASE_INSENSITIVE);

      try {
        PDDocument pdf = PDDocument.load(file);

        //Change the pdf to image
        for (int i=0; i< pdf.getNumberOfPages(); i++){
          file = DocumentVerification.pdfToImage(pdf, i);
        }

        Mat certificate = Imgcodecs.imread(file.getAbsolutePath());

        //System.out.println((CERTIFICATE_PATTERN.matcher(text).find()));
        if (logoMatched(coatOfArmsLogo, certificate) && (CERTIFICATE_PATTERN.matcher(text).find())){

          System.out.println("URSB certificate verified successfully!\n");

          return true;
        }else {

          System.out.println("URSB certificate verification failed!\n");

          return false;
        }




        } catch (IOException e) {
        throw new RuntimeException(e){
        };
      }

  }
  //Method to check if the coat of arms exists on the certificate
  public static boolean logoMatched(Mat logoImage, Mat documentImage) {
    // Step 1: Detect keypoints and descriptors using SIFT
    SIFT sift = SIFT.create();

    MatOfKeyPoint keypointsLogo = new MatOfKeyPoint();
    Mat descriptorsLogo = new Mat();
    //System.out.println("Logo descriptors"+descriptorsLogo.empty());

    MatOfKeyPoint keypointsDoc = new MatOfKeyPoint();
    Mat descriptorsDoc = new Mat();
    //System.out.println("Doc descriptors"+descriptorsDoc.empty());

    sift.detectAndCompute(logoImage, new Mat(), keypointsLogo, descriptorsLogo);
    sift.detectAndCompute(documentImage, new Mat(), keypointsDoc, descriptorsDoc);

    // Step 2: Match using BFMatcher and KNN
    BFMatcher matcher = BFMatcher.create(Core.NORM_L2, false);
    List<MatOfDMatch> knnMatches = new ArrayList<>();
    matcher.knnMatch(descriptorsLogo, descriptorsDoc, knnMatches, 2);

    // Step 3: Lowe’s ratio test
    float ratioThresh = 0.75f;
    List<DMatch> goodMatchesList = new ArrayList<>();

    for (MatOfDMatch matOfDMatch : knnMatches) {
      DMatch[] matches = matOfDMatch.toArray();
      if (matches.length >= 2 && matches[0].distance < ratioThresh * matches[1].distance) {
        goodMatchesList.add(matches[0]);
      }
    }

    // Step 4: Draw good matches
    Mat outputImage = new Mat();
    Features2d.drawMatches(
      logoImage, keypointsLogo,
      documentImage, keypointsDoc,
      new MatOfDMatch(goodMatchesList.toArray(new DMatch[0])),
      outputImage,
      Scalar.all(-1), Scalar.all(-1),
      new MatOfByte(),
      Features2d.DrawMatchesFlags_NOT_DRAW_SINGLE_POINTS
    );

    // Save the output image
    //Imgcodecs.imwrite("match.jpg", outputImage);

    // Step 5: Decision logic
    int matchThreshold = 5;
    System.out.println("Good matches found: " + goodMatchesList.size());

    return goodMatchesList.size() >= matchThreshold;
  }
}
