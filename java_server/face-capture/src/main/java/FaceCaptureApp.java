import org.opencv.core.Core;
import org.opencv.core.Mat;
import org.opencv.core.MatOfRect;
import org.opencv.core.Rect;
import org.opencv.highgui.HighGui;
import org.opencv.imgcodecs.Imgcodecs;
import org.opencv.imgproc.Imgproc;
import org.opencv.objdetect.CascadeClassifier;
import org.opencv.videoio.VideoCapture;

public class FaceCaptureApp {

  static {
    System.loadLibrary(Core.NATIVE_LIBRARY_NAME); // Load OpenCV native lib
  }

  public static Mat captureFace() {
    VideoCapture camera = new VideoCapture(0); // 0 = default webcam

    if (!camera.isOpened()) {
      System.out.println("❌ Cannot open the camera.");
      return null;
    }else System.out.println("Camera opened");


    // String cascadePath = "C:\\xampp\\htdocs\\Dairy_Supply_Chain_Management_System\\java_server\\Data\\haarcascade_frontalface_default.xml";

    ClassLoader classloader = FaceCaptureApp.class.getClassLoader();
    String cascadePath = classloader.getResource("Data/haarcascade_frontalface_default.xml").getPath();
    if (cascadePath == null) {
      System.err.println("❌ Cascade file not found in resources. Please check the path.");
      return null;
    }

    CascadeClassifier faceDetector = new CascadeClassifier(cascadePath);
    if (faceDetector.empty()) {
      System.err.println("❌ OpenCV failed to load the cascade file at: " + cascadePath);
      return null;
    }


    Mat frame = new Mat();
    boolean faceCaptured = false;

    // Show camera feed for 5 seconds before starting face detection
    System.out.println("🎥 Camera preview - Face detection will start in 5 seconds...");
    long startTime = System.currentTimeMillis();
    long previewDuration = 5000; // 5 seconds in milliseconds

    // Preview phase - just show camera feed without detection
    while (System.currentTimeMillis() - startTime < previewDuration) {
      camera.read(frame);
      if (!frame.empty()) {
        HighGui.imshow("Camera Preview - Get Ready!", frame);
        if (HighGui.waitKey(30) == 27) { // ESC to exit early
          System.out.println("Manual exit during preview");
          camera.release();
          HighGui.destroyAllWindows();
          return null;
        }
      }
    }

    while (!faceCaptured) {
      camera.read(frame); // Grab a frame

      if (frame.empty()) continue;

      Mat gray = new Mat();
      Imgproc.cvtColor(frame, gray, Imgproc.COLOR_BGR2GRAY);

      MatOfRect faces = new MatOfRect();
      faceDetector.detectMultiScale(gray, faces);

      for (Rect face : faces.toArray()) {
        if (face.width > 100 && face.height > 100) {
          // Crop and save grayscale face
          Mat faceROI = new Mat(gray, face);
          //Imgcodecs.imwrite("Capture.jpg", faceROI);
          //System.out.println("✅ Face saved at: " + outputPath);
          System.out.println("Face captured");
          HighGui.destroyAllWindows();
          camera.release();
          return faceROI;
        }
      }

      if(!faceCaptured){
      // Display live video in a popup window
      HighGui.imshow("Live Camera Feed", frame);
      if (HighGui.waitKey(30) == 27) break; // ESC key to manually exit
      }
    }

    HighGui.destroyAllWindows();
    camera.release(); // Release webcam
    return null;
  }

  public static void main(String[] args) {
    captureFace();
    System.exit(0);

    //System.out.println("Hello wold!!!!!");
  }

}
