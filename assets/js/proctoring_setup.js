function enableCamera() {
    const cameraCard = document.getElementById('camera-card');
    const cameraStatus = document.getElementById('camera-status');
    const micCard = document.getElementById('mic-card');

    // Simulating camera access
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: true }).then(function(stream) {
            cameraStatus.innerHTML = 'Camera enabled successfully!';
            cameraCard.classList.add('orange-border'); // Add orange border
            micCard.classList.remove('disabled'); // Unlock microphone step
        }).catch(function(err) {
            cameraStatus.innerHTML = 'Camera access denied.';
        });
    } else {
        cameraStatus.innerHTML = 'Camera not supported on this browser.';
    }
}

// Enable the microphone and unlock the next step
function enableMicrophone() {
    const micCard = document.getElementById('mic-card');
    const micStatus = document.getElementById('mic-status');
    const screenCard = document.getElementById('screen-card');

    // Simulating microphone access
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
            micStatus.innerHTML = 'Microphone enabled successfully!';
            micCard.classList.add('orange-border'); // Add orange border
            screenCard.classList.remove('disabled'); // Unlock screen sharing step
        }).catch(function(err) {
            micStatus.innerHTML = 'Microphone access denied.';
        });
    } else {
        micStatus.innerHTML = 'Microphone not supported on this browser.';
    }
}

// Enable screen sharing and unlock the test start button
function shareScreen() {
    const screenCard = document.getElementById('screen-card');
    const screenStatus = document.getElementById('screen-status');
    const readyCard = document.getElementById('ready-card');

    // Simulating screen sharing
    if (navigator.mediaDevices.getDisplayMedia) {
        navigator.mediaDevices.getDisplayMedia({ video: true }).then(function(stream) {
            screenStatus.innerHTML = 'Screen sharing enabled successfully!';
            screenCard.classList.add('orange-border'); // Add orange border
            readyCard.classList.remove('disabled'); // Unlock test start step
        }).catch(function(err) {
            screenStatus.innerHTML = 'Screen sharing access denied.';
        });
    } else {
        screenStatus.innerHTML = 'Screen sharing not supported on this browser.';
    }
}

// Start the test
function startTest() {
    window.location.href = '/start-test'; // Redirect to actual test page
}
