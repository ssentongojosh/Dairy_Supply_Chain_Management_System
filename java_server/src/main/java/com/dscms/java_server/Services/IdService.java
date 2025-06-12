package com.dscms.java_server.Services;

import org.springframework.http.ResponseEntity;
import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;

import java.util.regex.Pattern;

@Service
public class IdService {

    public ResponseEntity<?> verify (MultipartFile nationalId) {

        String text = DocumentVerification.extractText(nationalId);

        Pattern pattern = Pattern.compile("cupcake?", Pattern.CASE_INSENSITIVE);

       if(pattern.matcher(text).find()){//text.toLowerCase().contains("cupcake")(this can also be used instead of using the pattern)
           return ResponseEntity.ok("File processed successfully");
       }else
           return ResponseEntity.internalServerError().body("Invalid file");
    }

    //modify the validation logic
    //storing in the DB
}
