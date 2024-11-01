// google-login.js
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

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const provider = new GoogleAuthProvider();

provider.addScope('profile');

document.querySelector('#loginModal .oauthButton.google').addEventListener('click', (event) => {
  event.preventDefault();
  signInWithPopup(auth, provider)
    .then((result) => {
      const user = result.user;

      fetch("set_google_login_session.php", {
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
        if (data.includes("User not found")) {
          if (confirm("Account not registered. Would you like to register with this Google account?")) {
            // Close the login modal and open the signup modal
            $('#loginModal').modal('hide');
            $('#signupModal').modal('show');
          }
        } else {
          console.log("Google login session set:", data);
          window.location.href = "profile.php";
        }
      })
      .catch(error => console.error("Error setting Google login session:", error));
    })
    .catch((error) => {
      console.error("Google login error:", error);
    });
});
