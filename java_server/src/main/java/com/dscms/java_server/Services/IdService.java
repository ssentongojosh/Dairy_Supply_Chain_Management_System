package com.dscms.java_server.Services;

import org.apache.coyote.Response;
import org.springframework.http.ResponseEntity;
import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;

import java.util.regex.Pattern;

@Service
public class IdService {

    DocumentVerification documentVerification;
    public IdService(DocumentVerification documentVerification){
        this.documentVerification = documentVerification;
    }

    public boolean isVerified (MultipartFile nationalId) {

        String extractedText = documentVerification.extractText(nationalId);

        String text =  extractedText.replaceAll("\\s+", " ")  // Replace multiple spaces with single space
                                    .replaceAll("[^\\w\\s./-]", "")  // Remove special characters except common ones
                                    .trim()
                                    .toUpperCase();

        //Verification logic based on the extracted text

        return true;

        //Pattern pattern = Pattern.compile("cupcake?", Pattern.CASE_INSENSITIVE);

       /*if(pattern.matcher(text).find()){//text.toLowerCase().contains("cupcake")(this can also be used instead of using the pattern)
           return ResponseEntity.ok("File processed successfully");
       }else
           return ResponseEntity.internalServerError().body("Invalid file");*/
    }

}
