function button1Click() {
    window.location.href = 'login.php'; // Redirect to the Instructor Login page
}

function button2Click() {
    window.location.href = 'login.php'; // Redirect to the Participant Login page
}

const navLink = document.getElementById('help-support-link');
navLink.addEventListener('click', function (e) {
    e.preventDefault();
    navLink.classList.add('active');
});