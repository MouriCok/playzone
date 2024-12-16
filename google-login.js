// google-login.js
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
import { getAuth, signInWithPopup, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-auth.js";

async function getFirebaseConfig() {
  const response = await fetch('/firebase-config.php');
  return response.json();
}

(async () => {
  const firebaseConfig = await getFirebaseConfig();

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
            console.log("Google login session set:", data);
            window.location.href = "profile.php";
        })
        .catch(error => console.error("Error setting Google login session:", error));
      })
      .catch((error) => {
        console.error("Google login error:", error);
      });
  });
})();
