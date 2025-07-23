package com.dscms.java_server.Services;

import org.apache.pdfbox.pdmodel.PDDocument;
import org.opencv.core.Mat;
import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;

import java.io.File;
import java.io.IOException;
import java.util.regex.Pattern;

@Service
public class IdService {

  private final DocumentVerification documentVerification;
  public IdService(DocumentVerification documentVerification){
    this.documentVerification = documentVerification;
  }

  public boolean isVerified (MultipartFile nationalId) {
    System.out.println("\nVerifying National ID...");

    File file = null;
    try {
      //load the pdf from memory
      file = DocumentVerification.loadpdf(nationalId);

      //Extract the text from the pdf
      String extractedText = documentVerification.extractText(file);

      //Clean the text got from the pdf
      String text =  extractedText.replaceAll("\\s+", " ")  // Replace multiple spaces with single space
                                  .replaceAll("[^\\w\\s./-]", "")  // Remove special characters except common ones
                                  .trim()
                                  .toUpperCase();

      //Check if the text generated contains a NIN
      final Pattern NIN = Pattern.compile(".*\\b(C[M|F])[A-Z0-9]{12}\\b.*");

      //Facial recognition
      /*System.out.println("Starting facial verification...");

      PDDocument doc = PDDocument.load(file);
      File idFile = DocumentVerification.pdfToImage(doc, 0);


      Mat idFace =  FacialRecognitionService.detectAndCropFace(idFile.getPath());

      Mat liveFace = FacialRecognitionService.captureFaceViaExternalApp();

      boolean faceMatches = FacialRecognitionService.compareFaces(idFace, liveFace);
      if (faceMatches){

        doc.close();
        System.out.println("Faces match");

      }else {

        doc.close();
        System.out.println("Faces don't match");
      }*/


      if (NIN.matcher(text).matches() /*&& faceMatches*/){

        System.out.println("National ID verified successfully!\n");

        return true;
      }else {
        System.out.println("National ID verification failed!\n");
        return false;
      }

    } /*catch (IOException e) {
      throw new RuntimeException(e);
    }*/
    finally{
      System.out.println("Ntional ID verification finished\n");
    }

  }

}
