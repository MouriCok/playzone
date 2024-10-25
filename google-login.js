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

// Initialize Firebase and Auth
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const provider = new GoogleAuthProvider();

// Login using Google in login modal
document.querySelector('#loginModal .oauthButton.google').addEventListener('click', (event) => {
  event.preventDefault();
  signInWithPopup(auth, provider)
    .then((result) => {
      const user = result.user;
      sessionStorage.setItem('user', JSON.stringify(user)); // Save user details for profile.php
      window.location.href = "profile.php";
    })
    .catch((error) => {
      console.error("Google login error:", error);
    });
});
