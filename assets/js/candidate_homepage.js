const navbarToggle = document.getElementById('navbar-toggle');
const navLinks = document.getElementById('nav-links');

navbarToggle.addEventListener('click', () => {
    navLinks.classList.toggle('active');
});




document.addEventListener("DOMContentLoaded", () => {
    const confettiContainer = document.querySelector(".confetti");
    const section = document.getElementById("live-tournaments");
    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                createConfetti();
                sectionObserver.disconnect(); // Stop observing after triggering once
            }
        });
    });

    sectionObserver.observe(section);

    function createConfetti() {
        for (let i = 0; i < 150; i++) { // Increased the number of pieces
            const confettiPiece = document.createElement("div");
            confettiPiece.classList.add("confetti-piece");

            // Randomize position
            confettiPiece.style.left = Math.random() * 100 + "vw";
            confettiPiece.style.top = Math.random() * 100 + "vh"; // Start from random vertical position
            confettiPiece.style.backgroundColor = getRandomColor();
            confettiPiece.style.transform = `rotate(${Math.random() * 360}deg)`;
            confettiPiece.style.animationDuration = `${Math.random() * 3 + 2}s`; // Randomize fall duration

            confettiContainer.appendChild(confettiPiece);

            // Remove confetti piece after animation
            setTimeout(() => {
                confettiPiece.remove();
            }, 3000); // Keep longer on screen
        }
    }

    function getRandomColor() {
        const colors = ["#fa574e", "#ffcc00", "#6db4ff", "#ff6f61", "#b8e186"];
        return colors[Math.floor(Math.random() * colors.length)];
    }
});
