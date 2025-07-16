package com.dscms.java_server.Controllers;

import com.dscms.java_server.Requests.ValidationRequest;
import com.dscms.java_server.Services.IdService;
import com.dscms.java_server.Services.UrsbCertificateService;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
public class UploadController {

    private  final IdService idService;
    private final UrsbCertificateService ursbCertificateService;
    public UploadController(IdService idService, UrsbCertificateService ursbCertificateService){
        this.idService = idService;
        this.ursbCertificateService = ursbCertificateService;
    }

    @PostMapping("/verification")
    public ResponseEntity<?> fileUpload(@Valid @ModelAttribute ValidationRequest request){

      // Run both verifications independently to ensure both are processed
      boolean idVerified = idService.isVerified(request.getNationalId());
      boolean ursbVerified = ursbCertificateService.isVerified(request.getUrsbCertificate());

      if(idVerified && ursbVerified){
        return  ResponseEntity.ok("Verified successfully");
      }else {
        String message = "Verification failed! ";
        if (!idVerified) message += "National ID verification failed. ";
        if (!ursbVerified) message += "URSB Certificate verification failed. ";
        message += "Please upload files of compatible types with clear, readable content.";

        return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(message);
      }

    }

}
