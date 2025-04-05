// Function to show the selected section and handle FAQs scroll behavior
function showSection(targetSectionId) {
    const sections = document.querySelectorAll('.section');
    const gridContainer = document.querySelector('.grid-container');
    const dashboardContent = document.querySelector('.dashboard-content'); // Main dashboard content
    const calendarContainer = document.querySelector('.calendar-container'); // Calendar container
    const faqSection = document.getElementById('faq-section');

    // Hide all individual sections
    sections.forEach(section => {
        section.style.display = 'none';
    });

    // Hide the FAQ section explicitly when switching sections
    faqSection.style.display = 'none';

    gridContainer.style.display = 'none';

    if (targetSectionId) {
        if (targetSectionId === 'faq-section') {
            // Special logic for FAQs
            gridContainer.style.display = 'grid';
            dashboardContent.style.display = 'block'; // Show the main dashboard
            calendarContainer.style.display = 'block'; // Show the calendar
            faqSection.style.display = 'block'; // Ensure FAQ section is visible
            faqSection.scrollIntoView({ behavior: 'smooth' }); // Smooth scroll to FAQs
        } else {
            const targetSection = document.getElementById(targetSectionId);
            if (targetSection) {
                console.log(`Displaying section: ${targetSectionId}`);
                targetSection.style.display = 'block'; // Make the selected section visible

                // Special logic for Statistics section
                if (targetSectionId === 'statistics-section') {
                    loadQuizStatistics(); // Load statistics for the section
                }
            } else {
                console.error(`Section with ID "${targetSectionId}" not found.`);
            }
        }
    } else {
        // Default to showing the dashboard content with the calendar (Main page)
        gridContainer.style.display = 'grid';
        dashboardContent.style.display = 'block';
        calendarContainer.style.display = 'block';
        faqSection.style.display = 'block';
    }
}


// Sidebar menu click functionality
document.querySelectorAll('.sidebar-menu li').forEach(item => {
    item.addEventListener('click', () => {
        const targetSectionId = item.getAttribute('data-target');

        if (targetSectionId) {
            showSection(targetSectionId); // Show the selected section
        } else {
            showSection(); // Show the dashboard content (main page)
        }

        // Highlight the active menu item
        document.querySelectorAll('.sidebar-menu li').forEach(li => li.classList.remove('active'));
        item.classList.add('active');
    });
});

// Handle edit button click
document.getElementById('edit-profile-btn').addEventListener('click', () => {
    document.getElementById('profile-name').innerHTML = `<input type="text" id="edit-name" value="${document.getElementById('profile-name').textContent}" />`;
    document.getElementById('profile-email').innerHTML = `<input type="email" id="edit-email" value="${document.getElementById('profile-email').textContent}" />`;
    document.getElementById('profile-gender').innerHTML = `
        <select id="edit-gender">
            <option value="Male" ${document.getElementById('profile-gender').textContent === 'Male' ? 'selected' : ''}>Male</option>
            <option value="Female" ${document.getElementById('profile-gender').textContent === 'Female' ? 'selected' : ''}>Female</option>
            <option value="Other" ${document.getElementById('profile-gender').textContent === 'Other' ? 'selected' : ''}>Other</option>
        </select>`;
    document.getElementById('edit-profile-btn').style.display = 'none';
    document.getElementById('save-profile-btn').style.display = 'inline';
});

// Handle save button click
document.getElementById('save-profile-btn').addEventListener('click', () => {
    document.getElementById('profile-name').textContent = document.getElementById('edit-name').value;
    document.getElementById('profile-email').textContent = document.getElementById('edit-email').value;
    document.getElementById('profile-gender').textContent = document.getElementById('edit-gender').value;
    document.getElementById('edit-profile-btn').style.display = 'inline';
    document.getElementById('save-profile-btn').style.display = 'none';
});

// My Quizzes
// function loadQuizzesData() {
//     const quizzesTableBody = document.querySelector('#quizzes-table tbody');
    
//     // Mock data (replace this with real data from an API or database)
//     const quizzes = [
//         { title: "Quiz 1", createdOn: "2024-11-01", status: "Active" },
//         { title: "Quiz 2", createdOn: "2024-10-21", status: "Inactive" },
//         { title: "Quiz 3", createdOn: "2024-11-20", status: "Active" },
//     ];

//     // Clear the current table rows
//     quizzesTableBody.innerHTML = '';

//     // Add new rows from quizzes data
//     quizzes.forEach((quiz, index) => {
//         const row = document.createElement('tr');
        
//         row.innerHTML = `
//             <td>${index + 1}</td> <!-- S no. -->
//             <td>${quiz.title}</td>
//             <td>${quiz.createdOn}</td>
//             <td>${quiz.status}</td>
//             <td>
//                 <button>Edit</button> 
//                 <button>Delete</button>
//             </td>
//         `;
        
//         quizzesTableBody.appendChild(row);
//     });
// }

// Function to load and display quiz statistics (charts)
function loadQuizStatistics() {
    const statisticsSection = document.getElementById('statistics-section');

    // Mock data for demonstration purposes
    const quizData = [
        { name: "Quiz 1", averageScore: 85, activeUsers: 30 },
        { name: "Quiz 2", averageScore: 78, activeUsers: 25 },
        { name: "Quiz 3", averageScore: 88, activeUsers: 40 }
    ];

    // Render Bar Chart for Average Scores
    const averageScoreCtx = document.getElementById('averageScoreChart').getContext('2d');
    new Chart(averageScoreCtx, {
        type: 'bar',
        data: {
            labels: quizData.map(quiz => quiz.name),
            datasets: [{
                label: 'Average Score',
                data: quizData.map(quiz => quiz.averageScore),
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                borderColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Average Scores by Quiz' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Render Pie Chart for Active Users
    const activeUsersCtx = document.getElementById('activeUsersChart').getContext('2d');
    new Chart(activeUsersCtx, {
        type: 'pie',
        data: {
            labels: quizData.map(quiz => quiz.name),
            datasets: [{
                data: quizData.map(quiz => quiz.activeUsers),
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'bottom' },
                title: { display: true, text: 'Active Users per Quiz' }
            }
        }
    });
}

// Calendar generation function
function generateCalendar() {
    const calendar = document.getElementById('calendar');
    const today = new Date();
    const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    const month = today.getMonth();
    const year = today.getFullYear();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    const daysInMonth = lastDay.getDate();
    const firstDayIndex = firstDay.getDay();

    let calendarHTML = `<table>
        <caption>${monthNames[month]} ${year}</caption>
        <thead>
            <tr>
                <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
            </tr>
        </thead>
        <tbody><tr>`;

    // Empty cells for days before the start of the month
    for (let i = 0; i < firstDayIndex; i++) {
        calendarHTML += `<td></td>`;
    }

    // Fill in the days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const isToday = day === today.getDate();
        calendarHTML += `<td class="${isToday ? 'today' : ''}">${day}</td>`;
        if ((firstDayIndex + day) % 7 === 0) {
            calendarHTML += `</tr><tr>`;
        }
    }

    calendarHTML += `</tr></tbody></table>`;
    calendar.innerHTML = calendarHTML;
}

// Chart.js - Performance chart setup
const ctx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Quiz 1', 'Quiz 2', 'Quiz 3', 'Quiz 4', 'Quiz 5'],
        datasets: [{
            label: 'Scores',
            data: [85, 90, 75, 95, 88],
            backgroundColor: '#fa574e'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});

// Initialize the default view (dashboard content)
document.addEventListener('DOMContentLoaded', () => {
    showSection(); // Show dashboard content (main page) by default
    generateCalendar();
});

//for faq 
const accordionItems = document.querySelectorAll('.accordion-item');
// Function to toggle the accordion item
const toggleItem = (item) => {
    const content = item.querySelector('.accordion-content');
    const icon = item.querySelector('.accordion-title i'); // Select the icon element

    if (item.classList.contains('open')) {
        content.removeAttribute('style');
        item.classList.remove('open');
        icon.classList.remove('fa-chevron-up'); // Change the icon to down arrow
        icon.classList.add('fa-chevron-down');
    } else {
        content.style.maxHeight = content.scrollHeight + 'px';
        item.classList.add('open');
        icon.classList.remove('fa-chevron-down'); // Change the icon to up arrow
        icon.classList.add('fa-chevron-up');
    }
};

// Automatically open the first accordion item on page load
window.addEventListener('load', () => {
    const firstItem = accordionItems[0];
    toggleItem(firstItem);
});

accordionItems.forEach(item => {
    const title = item.querySelector('.accordion-title');
    title.addEventListener('click', () => {
        const openItem = document.querySelector('.accordion-item.open');

        toggleItem(item);

        if (openItem && openItem !== item) {
            toggleItem(openItem);
        }
    });
});