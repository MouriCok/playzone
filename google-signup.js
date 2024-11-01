// google-signup.js
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
import { getAuth, signInWithPopup, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-auth.js";

const firebaseConfig = {
  apiKey: "AIzaSyAReWs3S2W9gOeUQTodUThReNu1DAJ6Kug",
  authDomain: "playzone-system.firebaseapp.com",
  projectId: "playzone-system",
  storageBucket: "playzone-system.appspot.com",
  messagingSenderId: "497865306777",
  appId: "1:497865306777:web:eb3f01f83399d3666afe08",
  measurementId: "G-2R5XHP6QNM"
};

// Initialize Firebase and Auth
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const provider = new GoogleAuthProvider();

provider.addScope('profile');

document.querySelector('#signupModal .authButton.google').addEventListener('click', (event) => {
  event.preventDefault();
  signInWithPopup(auth, provider)
    .then((result) => {
      const user = result.user;

      // Send user data to PHP via AJAX to attempt registration
      fetch("set_google_signup_session.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          displayName: user.displayName,
          email: user.email,
          photoURL: user.photoURL || "default_avatar.png",
          uid: user.uid
        })
      })
      .then(response => response.text())
      .then((data) => {
        if (data.includes("Account already registered")) {
          if (confirm("Account already registered. Would you like to log in instead?")) {
            // Close the signup modal and open the login modal
            $('#signupModal').modal('hide');
            $('#loginModal').modal('show');
          }
        } else {
          console.log("Google signup session set:", data);
          window.location.href = "profile.php";
        }
      })
      .catch(error => console.error("Error setting Google signup session:", error));
    })
    .catch((error) => {
      console.error("Google signup error:", error);
    });
});
