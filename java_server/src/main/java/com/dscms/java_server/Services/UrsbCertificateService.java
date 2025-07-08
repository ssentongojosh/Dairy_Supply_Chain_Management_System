package com.dscms.java_server.Services;

import org.apache.pdfbox.pdmodel.PDDocument;
import org.opencv.core.*;
import org.opencv.features2d.BFMatcher;
import org.opencv.imgcodecs.Imgcodecs;
import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;
import org.springframework.core.io.ClassPathResource;
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

        File pdfFile = null;
        File imageFile = null;

        try {
            //Load the pdf
            pdfFile = DocumentVerification.loadpdf(ursbCertificate);

            // Load the logo template using relative path from resources
            Mat coatOfArmsLogo = loadLogoFromResources();

            // Check if the logo image was loaded successfully
            if (coatOfArmsLogo.empty()) {
                System.err.println("Error: Could not load Coat of Arms logo from resources.");
                System.err.println("Falling back to text-only verification.");
                // Fall back to text-only verification
                String text = documentVerification.extractText(pdfFile);
                final Pattern CERTIFICATE_PATTERN = Pattern.compile("Certificate\\s+of\\s+[HIli1|]ncorporation", Pattern.CASE_INSENSITIVE);

                if (CERTIFICATE_PATTERN.matcher(text).find()) {
                    System.out.println("URSB certificate verified successfully (text-only verification)!\n");
                    return true;
                } else {
                    System.out.println("URSB certificate verification failed (text-only verification)!\n");
                    return false;
                }
            }

            //Extract the text from the certificate
            String text = documentVerification.extractText(pdfFile);

            //Generate a pattern to check if the certificate contains "Certificate Of Incorporation on it"
            final Pattern CERTIFICATE_PATTERN = Pattern.compile("Certificate\\s+of\\s+[HIli1|]ncorporation", Pattern.CASE_INSENSITIVE);

            // Use try-with-resources to ensure PDDocument is properly closed
            try (PDDocument pdf = PDDocument.load(pdfFile)) {

                //Change the pdf to image
                for (int i=0; i< pdf.getNumberOfPages(); i++){
                    imageFile = DocumentVerification.pdfToImage(pdf, i);
                }

                Mat certificate = Imgcodecs.imread(imageFile.getAbsolutePath());

                // Check if certificate image was loaded successfully
                if (certificate.empty()) {
                    System.err.println("Error: Could not load certificate image. Falling back to text-only verification.");
                    if (CERTIFICATE_PATTERN.matcher(text).find()) {
                        System.out.println("URSB certificate verified successfully (text-only verification)!\n");
                        return true;
                    } else {
                        System.out.println("URSB certificate verification failed (text-only verification)!\n");
                        return false;
                    }
                }

                if (logoMatched(coatOfArmsLogo, certificate) && (CERTIFICATE_PATTERN.matcher(text).find())){
                    System.out.println("URSB certificate verified successfully!\n");
                    return true;
                } else {
                    System.out.println("URSB certificate verification failed!\n");
                    return false;
                }

            } catch (IOException e) {
                throw new RuntimeException(e);
            }

        } finally {
            // Clean up temporary files
            if (pdfFile != null && pdfFile.exists()) {
                if (pdfFile.delete()) {
                    System.out.println("Temporary PDF file cleaned up successfully.");
                } else {
                    System.out.println("Warning: Could not delete temporary PDF file: " + pdfFile.getPath());
                }
            }
            if (imageFile != null && imageFile.exists()) {
                if (imageFile.delete()) {
                    System.out.println("Temporary image file cleaned up successfully.");
                } else {
                    System.out.println("Warning: Could not delete temporary image file: " + imageFile.getPath());
                }
            }
        }
    }

    /**
     * Load the Coat of Arms logo from resources using Spring's ClassPathResource
     * This ensures portability across different environments and deployment scenarios
     */
    private Mat loadLogoFromResources() {
        try {
            // Use ClassPathResource to load from src/main/resources/Images/COA.jpeg
            ClassPathResource logoResource = new ClassPathResource("Images/COA.jpeg");

            if (!logoResource.exists()) {
                System.err.println("Error: COA.jpeg not found in classpath at Images/COA.jpeg");
                return new Mat(); // Return empty Mat
            }

            // Get the file path from the resource
            String logoPath = logoResource.getFile().getAbsolutePath();
            System.out.println("Loading logo from: " + logoPath);

            // Load the image using OpenCV
            Mat logo = Imgcodecs.imread(logoPath, Imgcodecs.IMREAD_GRAYSCALE);

            if (logo.empty()) {
                System.err.println("Error: Failed to load logo image from: " + logoPath);
            } else {
                System.out.println("Logo loaded successfully from resources.");
            }

            return logo;

        } catch (IOException e) {
            System.err.println("Error loading logo from resources: " + e.getMessage());

            // Fallback: try loading from file system as last resort
            return loadLogoFromFileSystem();
        }
    }

    /**
     * Fallback method to load logo from file system
     * This is used as a last resort if ClassPathResource fails
     */
    private Mat loadLogoFromFileSystem() {
        System.out.println("Attempting fallback: loading logo from file system...");

        // Try multiple possible paths
        String[] possiblePaths = {
            "java_server/src/main/resources/Images/COA.jpeg",
            "src/main/resources/Images/COA.jpeg",
            "./src/main/resources/Images/COA.jpeg",
            "../src/main/resources/Images/COA.jpeg"
        };

        for (String path : possiblePaths) {
            System.out.println("Trying path: " + path);
            Mat logo = Imgcodecs.imread(path, Imgcodecs.IMREAD_GRAYSCALE);

            if (!logo.empty()) {
                System.out.println("Logo loaded successfully from: " + path);
                return logo;
            }
        }

        System.err.println("Failed to load logo from all attempted paths.");
        return new Mat(); // Return empty Mat
    }

    //Method to check if the coat of arms exists on the certificate
    public static boolean logoMatched(Mat logoImage, Mat documentImage) {

        // Check if images are valid before processing
        if (logoImage.empty() || documentImage.empty()) {
            System.err.println("Error: One or both images are empty. Cannot perform logo matching.");
            return false;
        }

        try {
            // Step 1: Detect keypoints and descriptors using SIFT
            SIFT sift = SIFT.create();

            MatOfKeyPoint keypointsLogo = new MatOfKeyPoint();
            Mat descriptorsLogo = new Mat();

            MatOfKeyPoint keypointsDoc = new MatOfKeyPoint();
            Mat descriptorsDoc = new Mat();

            sift.detectAndCompute(logoImage, new Mat(), keypointsLogo, descriptorsLogo);
            sift.detectAndCompute(documentImage, new Mat(), keypointsDoc, descriptorsDoc);

            // Check if descriptors were computed successfully
            if (descriptorsLogo.empty() || descriptorsDoc.empty()) {
                System.err.println("Error: Could not compute descriptors for logo matching.");
                return false;
            }

            // Step 2: Match using BFMatcher and KNN
            BFMatcher matcher = BFMatcher.create(Core.NORM_L2, false);
            List<MatOfDMatch> knnMatches = new ArrayList<>();
            matcher.knnMatch(descriptorsLogo, descriptorsDoc, knnMatches, 2);

            // Step 3: Lowe's ratio test
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

            // Step 5: Decision logic
            int matchThreshold = 5;
            System.out.println("Good matches found: " + goodMatchesList.size());

            return goodMatchesList.size() >= matchThreshold;

        } catch (Exception e) {
            System.err.println("Error during logo matching: " + e.getMessage());
            return false;
        }
    }
}
