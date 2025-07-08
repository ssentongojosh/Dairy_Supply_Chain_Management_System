package com.dscms.java_server.Services;

import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;

import java.io.File;
import java.util.regex.Pattern;

@Service
public class IdService {

    DocumentVerification documentVerification;
    public IdService(DocumentVerification documentVerification){
        this.documentVerification = documentVerification;
    }

    public boolean isVerified (MultipartFile nationalId) {
        System.out.println("\nVerifying National ID...\n");

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

            if (NIN.matcher(text).matches()){

              System.out.println("National ID verified successfully!\n");

              return true;
            }else {
              System.out.println("National ID verification failed!\n");
              return false;
            }

        } finally {
            // Clean up temporary PDF file
            if (file != null && file.exists()) {
                if (file.delete()) {
                    System.out.println("Temporary PDF file cleaned up successfully.");
                } else {
                    System.out.println("Warning: Could not delete temporary PDF file: " + file.getPath());
                }
            }
        }

    }

}
