function checkPermissions() {
  return Promise.all([
    navigator.permissions.query({ name: 'camera' }),
    navigator.permissions.query({ name: 'microphone' }),
    navigator.permissions.query({ name: 'screen' }),
  ]);
}

function reEnableMedia() {
  checkPermissions().then((permissions) => {
    // Only re-enable streams if permissions are granted
    const cameraPermission = permissions[0].state;
    const micPermission = permissions[1].state;
    const screenPermission = permissions[2].state;

    if (cameraPermission === 'granted' && micPermission === 'granted' && screenPermission === 'granted') {
      // Proceed with re-enabling media streams
      enableStreams();
    } else {
      console.warn('Permissions not granted for one or more media streams.');
    }
  }).catch(err => console.error('Error checking permissions:', err));
}

function enableStreams() {
  const cameraStreamId = sessionStorage.getItem('cameraStreamId');
  const micStreamId = sessionStorage.getItem('micStreamId');
  const screenStreamId = sessionStorage.getItem('screenStreamId');
  
  const cameraTracks = JSON.parse(sessionStorage.getItem('cameraTracks'));
  const micTracks = JSON.parse(sessionStorage.getItem('micTracks'));
  const screenTracks = JSON.parse(sessionStorage.getItem('screenTracks'));

  // Function to check if a stream is still active
  function isStreamActive(streamId) {
    try {
      const stream = streamId ? document.querySelector(`[srcObject='${streamId}']`) : null;
      return stream && stream.active;
    } catch (error) {
      return false;
    }
  }

  // Camera Stream Handling
  if (cameraTracks && !isStreamActive(cameraStreamId)) {
    const videoElement = document.getElementById('video-element'); // Your video element for camera
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(stream => {
        const videoTrack = stream.getTracks().find(track => cameraTracks.includes(track.id));
        if (videoTrack) {
          const videoStream = new MediaStream([videoTrack]);
          videoElement.srcObject = videoStream; // Attach to the video element
          console.log('Camera stream re-enabled.');
          sessionStorage.setItem('cameraStreamId', videoStream.id); // Store new stream ID
        }
      }).catch(err => console.error('Error re-enabling camera:', err));
  }

  // Microphone Stream Handling
  if (micTracks && !isStreamActive(micStreamId)) {
    const audioElement = document.getElementById('audio-element'); // Your audio element for microphone
    navigator.mediaDevices.getUserMedia({ audio: true })
      .then(stream => {
        const audioTrack = stream.getTracks().find(track => micTracks.includes(track.id));
        if (audioTrack) {
          const audioStream = new MediaStream([audioTrack]);
          audioElement.srcObject = audioStream; // Attach to the audio element
          console.log('Microphone stream re-enabled.');
          sessionStorage.setItem('micStreamId', audioStream.id); // Store new stream ID
        }
      }).catch(err => console.error('Error re-enabling microphone:', err));
  }

  // Screen Sharing Stream Handling
  if (screenTracks && !isStreamActive(screenStreamId)) {
    const screenElement = document.getElementById('screen-element'); // Your video element for screen sharing
    navigator.mediaDevices.getDisplayMedia({ video: true })
      .then(stream => {
        const screenTrack = stream.getTracks().find(track => screenTracks.includes(track.id));
        if (screenTrack) {
          const screenStream = new MediaStream([screenTrack]);
          screenElement.srcObject = screenStream; // Attach to the screen sharing element
          console.log('Screen sharing re-enabled.');
          sessionStorage.setItem('screenStreamId', screenStream.id); // Store new stream ID
        }
      }).catch(err => console.error('Error re-enabling screen sharing:', err));
  }
}

// Call reEnableMedia when the page loads
window.onload = reEnableMedia;




let currentQuestionIndex = 0;
    const questions = document.querySelectorAll('.question-container');
    const questionStatus = document.querySelectorAll('.question-number');

    // Function to check if the current question is answered
    const isAnswered = (index) => {
      const options = questions[index].querySelectorAll('input[type="radio"]');
      return [...options].some(option => option.checked); // Check if any option is selected
    };

    // Function to show the current question based on index
    const showQuestion = (index) => {
      questions.forEach((q, i) => {
        q.classList.toggle('active', i === index);
      });
    };

    // Function to update the question status (Answered, Unanswered, Not Visited)
    const updateStatus = (index) => {
      if (isAnswered(index)) {
        questionStatus[index].className = 'question-number answered';
      } else {
        questionStatus[index].className = 'question-number unanswered'; // Turn red for unanswered
      }
    };

    // Handle navigation (next and previous)
    const handleNavigation = (step) => {
      updateStatus(currentQuestionIndex); // Update status of the current question
      currentQuestionIndex = Math.min(Math.max(currentQuestionIndex + step, 0), questions.length - 1);
      showQuestion(currentQuestionIndex);
    };

    // Event listener for the "Next" button
    document.getElementById('next-btn').addEventListener('click', () => handleNavigation(1));

    // Event listener for the "Previous" button
    document.getElementById('prev-btn').addEventListener('click', () => handleNavigation(-1));

    // Event listener for the status circles (click to navigate to a specific question)
    questionStatus.forEach((qStatus, i) => {
      qStatus.addEventListener('click', () => {
        updateStatus(currentQuestionIndex); // Update status before changing question
        currentQuestionIndex = i;
        showQuestion(i);
      });
    });

    // Initialize question status when a choice is made
    questions.forEach((q, i) => {
      const options = q.querySelectorAll('input[type="radio"]');
      options.forEach(option => {
        option.addEventListener('change', () => {
          questionStatus[i].className = 'question-number answered';
        });
      });
    });

    // Show the first question initially
    showQuestion(currentQuestionIndex);