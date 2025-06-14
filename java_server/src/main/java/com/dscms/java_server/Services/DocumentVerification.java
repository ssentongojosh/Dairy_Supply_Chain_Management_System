package com.dscms.java_server.Services;

import net.sourceforge.tess4j.ITesseract;
import net.sourceforge.tess4j.Tesseract;
import net.sourceforge.tess4j.TesseractException;
import org.apache.pdfbox.pdmodel.PDDocument;
import org.apache.pdfbox.rendering.PDFRenderer;
import org.opencv.core.Core;
import org.opencv.core.Mat;
import org.opencv.core.Size;
import org.opencv.imgcodecs.Imgcodecs;
import org.opencv.imgproc.Imgproc;
import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;

import javax.imageio.ImageIO;
import java.awt.image.BufferedImage;
import java.awt.image.DataBufferByte;
import java.io.File;
import java.io.IOException;

@Service
public class DocumentVerification {

    //Static block to load the cpp files for OpenCV
    static {
        try {
            // Try loading with OpenCV's built-in loader first
            nu.pattern.OpenCV.loadShared();
        } catch (Exception e) {
            try {
                // Fallback to system library loading
                System.loadLibrary(Core.NATIVE_LIBRARY_NAME);
            } catch (UnsatisfiedLinkError ex) {
                // If both fail, try loading opencv_java directly
                try {
                    System.loadLibrary("opencv_java");
                } catch (UnsatisfiedLinkError ex2) {
                    System.err.println("Failed to load OpenCV native library. Please ensure OpenCV is properly installed.");
                    throw new RuntimeException("OpenCV native library not found", ex2);
                }
            }
        }
    }

    //Method to load pdf from memory
    public static  PDDocument loadpdf(MultipartFile file){
        try {
            //Store a temporary file
            File tempFile = File.createTempFile("file",".pdf");

            //Transfer the pdf file to the tempFile memory
            file.transferTo(tempFile);

            //Load the pdf
            return PDDocument.load(tempFile);

        } catch (IOException e) {
            throw new RuntimeException(e);
        }
    }

    //Method to turn pdf to image
    public static File pdfToImage(PDDocument doc, int pageIndex){

        //create a PDFRenderer object to turn the pdf to image
        PDFRenderer renderer = new PDFRenderer(doc);

        try {
            //Turn the pdf to image
            BufferedImage image = renderer.renderImageWithDPI(pageIndex,300);

            //Create a temporary file to store the image produced
            File img = File.createTempFile("Page_", ".png");

            //Store the image in the file
            ImageIO.write(image,"png",img);

            return img;

        } catch (IOException e) {
            throw new RuntimeException(e);
        }
    }

    //Method to preProcess the image to make it better for OCR
    public static Mat preProcessing(String path){

        //Read the image from the memory using the path
        Mat mat = Imgcodecs.imread(path);

        if(mat.empty()){
            throw new RuntimeException("Image not found");
        }

        //Turn the image to gray format
        Mat gray = new Mat();
        Imgproc.cvtColor(mat, gray, Imgproc.COLOR_BGR2GRAY);

        //(Optional)You can blur the image using the Imgproc.GaussianBlur() method to reduce background noise
        Mat blurredImage = new Mat();
        Imgproc.GaussianBlur(gray, blurredImage, new Size(5,5),0);

        return blurredImage;
    }

    //Method to turn a Mat object(the preprocessed image in this case) to a buffered image
    public static BufferedImage matToBufferedImage(Mat mat){
        //Determine the BufferedImage type based on channels
        int type = BufferedImage.TYPE_BYTE_GRAY;
        if (mat.channels() > 1) {
            type = BufferedImage.TYPE_3BYTE_BGR;
        }

        //Calculate total buffer size needed
        int bufferSize = mat.channels() * mat.cols() * mat.rows();

        //Create byte array to hold pixel data
        byte[] buffer = new byte[bufferSize];

        //Extract all pixel data from Mat into byte array
        mat.get(0, 0, buffer); // get all pixels

        //Create BufferedImage with calculated dimensions and type
        BufferedImage image = new BufferedImage(mat.cols(), mat.rows(), type);

        //Get direct access to BufferedImage's internal pixel data
        final byte[] targetPixels = ((DataBufferByte) image.getRaster().getDataBuffer()).getData();

        //Copy pixel data from our buffer to BufferedImage's internal buffer
        System.arraycopy(buffer, 0, targetPixels, 0, buffer.length);

        return image;
    }

    //Method to do OCR on the image
    public static String ocr(BufferedImage img){

        try {

            //Instantiate a  tesseract object that will do the OCR
            ITesseract tesseract = new Tesseract();
            //Set the path that has the tesseract data
            tesseract.setDatapath("C:/Tesseract-OCR/tessdata");
            //Set the language of the text that tesseract will extract when performing the OCR
            tesseract.setLanguage("eng");

            return tesseract.doOCR(img);

        } catch (TesseractException e) {
            throw new RuntimeException(e);
        }

    }

    //Method to extract text from the pdf
    public  String extractText(MultipartFile file){

        StringBuilder extractedText = new StringBuilder();

        try{
            PDDocument document = loadpdf(file);

            for (int i=0; i<document.getNumberOfPages(); i++){

                File image = pdfToImage(document, i);

                Mat preProcessedImage = preProcessing(image.getPath());

                BufferedImage bufferedImage = matToBufferedImage(preProcessedImage);

                String text = ocr(bufferedImage);

                extractedText.append(text).append("\n");
            }

            document.close();

            return extractedText.toString();

        } catch (IOException e) {
            throw new RuntimeException(e);
        }

    }

}
