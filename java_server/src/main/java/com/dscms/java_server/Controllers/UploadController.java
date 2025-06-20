package com.dscms.java_server.Controllers;

import com.dscms.java_server.Requests.ValidationRequest;
import com.dscms.java_server.Services.BankStatementService;
import com.dscms.java_server.Services.IdService;
import com.dscms.java_server.Services.UrsbCertificateService;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
public class UploadController {

    private  final IdService idService;
    private final BankStatementService bankStatementService;
    private final UrsbCertificateService ursbCertificateService;
    public UploadController(IdService idService,BankStatementService bankStatementService, UrsbCertificateService ursbCertificateService){
        this.idService = idService;
        this.bankStatementService = bankStatementService;
        this.ursbCertificateService = ursbCertificateService;
    }

    @PostMapping("/verified")
    public ResponseEntity<?> fileUpload(@ModelAttribute ValidationRequest request){

      if(idService.isVerified(request.getNationalId()) && ursbCertificateService.isVerified(request.getUrsbCertificate())){
        return  ResponseEntity.ok("Verified successfully");
      }else
        return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body("Verification failed! Please upload a file of a compatible type and the contents on must be clear");

    }

}
