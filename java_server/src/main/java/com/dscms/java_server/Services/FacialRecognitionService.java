package com.dscms.java_server.Services;

import org.bytedeco.javacpp.DoublePointer;
import org.bytedeco.javacpp.IntPointer;
import org.bytedeco.opencv.global.opencv_core;
import org.bytedeco.opencv.opencv_core.MatVector;
import org.bytedeco.opencv.opencv_face.LBPHFaceRecognizer;
import org.opencv.core.*;
import org.opencv.imgcodecs.Imgcodecs;
import org.opencv.objdetect.CascadeClassifier;
import org.springframework.boot.autoconfigure.condition.ConditionalOnProperty;
import org.springframework.stereotype.Service;


import java.io.BufferedReader;
import java.io.File;
import java.io.InputStreamReader;

import static org.opencv.imgproc.Imgproc.*;

@Service
@ConditionalOnProperty(name = "facial.recognition.enabled", havingValue = "true", matchIfMissing = false)
public class FacialRecognitionService {

  private static boolean openCVLoaded = false;

  static {
    loadOpenCV();
  }

  private static synchronized void loadOpenCV() {
    if (!openCVLoaded) {
      try {
        System.loadLibrary(Core.NATIVE_LIBRARY_NAME);
        openCVLoaded = true;
        System.out.println("OpenCV library loaded successfully");
      } catch (UnsatisfiedLinkError e) {
        // Library might already be loaded
        if (e.getMessage().contains("already loaded")) {
          openCVLoaded = true;
          System.out.println("OpenCV library was already loaded");
        } else {
          System.err.println("Failed to load OpenCV library: " + e.getMessage());
          throw e;
        }
      }
    }
  }

  public static Mat detectAndCropFace(String imagePath) {
    String cascadePath = "Data/haarcascade_frontalface_alt.xml";
    CascadeClassifier faceDetector = new CascadeClassifier(cascadePath);
    //faceDetector.load(cascadePath);

    if (!faceDetector.load(cascadePath)) {
      System.err.println("Error: Could not load cascade classifier");
      return null; // or throw exception
    }

    Mat image = Imgcodecs.imread(imagePath);
    Mat gray = new Mat();
    cvtColor(image, gray, COLOR_BGR2GRAY);

    MatOfRect faceDetections = new MatOfRect();
    faceDetector.detectMultiScale(gray, faceDetections);



    for (Rect rect : faceDetections.toArray()) {
      //Imgproc.rectangle(frame, new Point(rect.x, rect.y), new Point(rect.x + rect.width, rect.y + rect.height), new Scalar(0, 255, 0),2);
      Mat frame = new Mat(gray, rect);
      //Imgcodecs.imwrite("ID image.jpg", frame);
      System.out.println("Face extracted from ID");
      return frame; // Crop and return grayscale face
    }

    return null; // No face found
  }

  public static Mat captureFaceViaExternalApp() {
    try {
      String cascadePath = "C:\\xampp\\htdocs\\Dairy_Supply_Chain_Management_System\\java_server\\Data\\haarcascade_frontalface_alt.xml";

      ProcessBuilder builder = new ProcessBuilder(
        "java", "-Djava.library.path=C:/opencv/build/java/x64", "-jar", "FaceCaptureApp.jar", cascadePath
      );
      builder.directory(new File("C:/xampp/htdocs/Dairy_Supply_Chain_Management_System/java_server/face-capture/out/artifacts/face_capture_jar")); // optional
      //builder.inheritIO(); // to show console output

      Process process = builder.start();

      String imagePath = null;
      try (BufferedReader reader = new BufferedReader(new InputStreamReader(process.getInputStream()))) {
        String line;
        while ((line = reader.readLine()) != null) {
          if (line.startsWith("SUCCESS:")) {
            imagePath = line.substring("SUCCESS:".length());
            System.out.println("Image at: " + imagePath);
          }
        }
      }

      process.waitFor();

      if (imagePath == null) {
        System.err.println("No success message received from external app");
        return null;
      }

      //Load image as Mat
      Mat face;
      File imgFile = new File(imagePath);

      if (!imgFile.exists()){
        System.err.println("Image file not found: " + imagePath);
        return null;
      }

      face = Imgcodecs.imread(imagePath, Imgcodecs.IMREAD_GRAYSCALE);

      //Delete image file
      imgFile.delete();

      return face;
    } catch (Exception e) {
      e.printStackTrace();
      return null;
    }
  }

  public static boolean compareFaces(Mat idFace, Mat liveFace) {
    LBPHFaceRecognizer recognizer = LBPHFaceRecognizer.create();

    Mat preprocessedIdFace = preprocessFace(idFace);
    Mat preprocessedLiveFace = preprocessFace(liveFace);

    // Training with ID image
    MatVector images = new MatVector(1);
    images.put(0, toBytedecoMat(idFace));
    org.bytedeco.opencv.opencv_core.Mat labels = new org.bytedeco.opencv.opencv_core.Mat(1, 1, opencv_core.CV_32SC1);
    labels.ptr(0).putInt(1);
    recognizer.train(images, labels);

    // Predict with live face
    IntPointer label = new IntPointer(1);
    DoublePointer confidence = new DoublePointer(1);
    recognizer.predict(toBytedecoMat(liveFace), label, confidence);

    System.out.println("Label: " + label.get() + " Confidence: " + confidence.get());
    System.out.println(label.get() == 1 && confidence.get() < 60);

    if (label.get() == 1 && confidence.get() < 60){
      System.out.println("Faces match");
      return true;
    }else{
      System.out.println("Faces don't match");
      return false;
    }
  }

  private static org.bytedeco.opencv.opencv_core.Mat toBytedecoMat(Mat javaMat) {

    if (javaMat == null || javaMat.empty()) {
      throw new IllegalArgumentException("Input Mat is null or empty");
    }

    byte[] data = new byte[(int) (javaMat.total() * javaMat.channels())];
    javaMat.get(0, 0, data);
    org.bytedeco.opencv.opencv_core.Mat bytedecoMat = new org.bytedeco.opencv.opencv_core.Mat(
      javaMat.rows(), javaMat.cols(), javaMat.type()
    );
    bytedecoMat.data().put(data);
    return bytedecoMat;
  }

  // Preprocessing function
  private static Mat preprocessFace(Mat face) {
    Mat processed = new Mat();

    //Convert to grayscale if not already
    if (face.channels() > 1) {
      cvtColor(face, processed, COLOR_BGR2GRAY);
    } else {
      processed = face.clone();
    }

    //Histogram equalization (normalize lighting)
    Mat equalized = new Mat();
    equalizeHist(processed, equalized);

    //Resize to consistent dimensions (200x200)
    Mat resized = new Mat();
    resize(equalized, resized, new Size(200, 200));

    return resized;
  }

}
