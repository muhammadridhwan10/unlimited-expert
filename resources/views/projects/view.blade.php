@extends('layouts.admin')
@section('page-title')
    {{ucwords($project->project_name)}}
@endsection
@push('css-page')
    <style>
        @import url({{ asset('css/font-awesome.css') }});

        .dropdown-menu {
            max-height: 200px;
            overflow-y: auto;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
        }

        .dropdown-item input[type="checkbox"] {
            margin-right: 10px;
        }

        .dropdown-menu {
            max-height: 250px;
            overflow-y: auto;
        }

        #comment-form {
            gap: 10px;
        }

        #comment-text {
            resize: none;
        }

        #calendar {
            min-height: 500px; 
        }

        .card {
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
        }

        @media (max-width: 768px) {

            .row > .col-md-8,
            .row > .col-md-4 {
                flex: 100%; 
            }

            #comment-form {
                flex-direction: column;
            }
        }

        #comment-list {
            max-height: 380px;
            overflow-y: auto;
            padding: 15px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
        }

        .comment-item {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); 
            animation: fadeIn 0.5s ease-in-out; 
        }

        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .comment-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }

        .comment-header .user-info {
            font-weight: bold;
            color: #333;
            font-size: 1rem;
        }

        .comment-header .timestamp {
            font-size: 0.8rem;
            color: #888;
            margin-left: auto;
        }

        .comment-body {
            margin-bottom: 10px;
            color: #555;
            line-height: 1.5; 
        }

        .comment-file {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9em;
            display: inline-block;
            margin-top: 5px;
        }

        .comment-file:hover {
            text-decoration: underline;
        }

        #comment-form {
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 30px;
            padding: 10px 15px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        #comment-text {
            flex-grow: 1;
            border: none;
            outline: none;
            padding: 10px;
            font-size: 1rem;
            border-radius: 20px;
            background: #ffffff;
        }

        #comment-text::placeholder {
            color: #aaa;
        }

        #comment-file-label {
            cursor: pointer;
            color: #888;
            font-size: 1.3rem;
            margin: 0 10px;
            transition: color 0.3s;
        }

        #comment-file-label:hover {
            color: #555;
        }

        #send-button {
            background: none;
            border: none;
            cursor: pointer;
            color: #000000;
            font-size: 1.5rem;
            transition: color 0.3s;
        }

        #send-button:hover {
            color: #0056b3;
        }

        /* Responsif */
        @media (max-width: 768px) {
            #comment-form {
                flex-direction: row;
                align-items: center;
            }

            #comment-text {
                width: 100%;
            }

            #comment-file-label, #send-button {
                font-size: 1.2rem;
            }
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #calendar {
            max-width: 100%;
            height: 500px;
            margin: 0 auto;
        }

    </style>
@endpush
@push('script-page')
    <script src="{{ asset('js/bootstrap-toggle.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');
        $('document').ready(function () {
            $('.toggleswitch').bootstrapToggle();
            $("fieldset[id^='demo'] .stars").click(function () {
                alert($(this).val());
                $(this).attr("checked");
            });
        });

        $(document).ready(function () {
            const projectId = {{ $project->id }};

            function fetchNotesByUser() {
                $.ajax({
                    url: `${baseUrl}/projects/${projectId}/notes`,
                    method: "GET",
                    success: function (data) {
                        const container = $('#notes-by-user-container');
                        container.empty();

                        if (data.length === 0) {
                            container.append('<p class="text-muted">No notes available for this project.</p>');
                            return;
                        }

                        data.forEach(user => {
                            const accordionId = `user-${user.id}`;
                            const userCard = `
                                <div class="accordion mb-3" id="${accordionId}">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading-${accordionId}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${accordionId}" aria-expanded="false" aria-controls="collapse-${accordionId}">
                                                ${user.name} (${user.notes.length} notes)
                                            </button>
                                        </h2>
                                        <div id="collapse-${accordionId}" class="accordion-collapse collapse" aria-labelledby="heading-${accordionId}" data-bs-parent="#${accordionId}">
                                            <div class="accordion-body">
                                                <ul class="list-group list-group-flush">
                                                    ${user.notes.map(note => `
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <input type="checkbox" class="me-2 toggle-status" data-id="${note.id}" ${note.is_completed ? 'checked' : ''}>
                                                                <span class="${note.is_completed ? 'text-decoration-line-through text-muted' : ''}">${note.content}</span>
                                                            </div>
                                                            <div>
                                                                <button class="btn btn-danger btn-sm delete-note" data-id="${note.id}">
                                                                <i class="ti ti-trash"></i>
                                                                </button>
                                                            </div>
                                                        </li>
                                                    `).join('')}
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            container.append(userCard);
                        });
                    },
                    error: function (error) {
                        console.error("Error fetching notes:", error);
                    },
                });
            }

            $('#add-note-form').on('submit', function (e) {
                e.preventDefault();
                const content = $('#note-content').val();

                $.ajax({
                    url: `${baseUrl}/projects/${projectId}/notes`,
                    method: "POST",
                    data: { content },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function () {
                        $('#note-content').val('');
                        show_toastr('{{__('success')}}', '{{ __("Notes Added Successfully!")}}');
                        fetchNotesByUser();
                    },
                    error: function (error) {
                        console.error("Error adding note:", error);
                    },
                });
            });

            $(document).on('change', '.toggle-status', function () {
                const noteId = $(this).data('id');

                $.ajax({
                    url: `${baseUrl}/projects/notes/${noteId}/status`,
                    method: "PUT",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function () {
                        fetchNotesByUser();
                    },
                    error: function (error) {
                        console.error("Error updating note status:", error);
                    },
                });
            });

            $(document).on('click', '.delete-note', function () {
                const noteId = $(this).data('id');

                $.ajax({
                    url: `${baseUrl}/projects/notes/${noteId}`,
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function () {
                        fetchNotesByUser();
                    },
                    error: function (error) {
                        console.error("Error deleting note:", error);
                    },
                });
            });

            fetchNotesByUser();
        });

        $(document).ready(function () {
            const projectId = {{ $project->id }};
            const apiUrl = `${baseUrl}/projects/${projectId}/report-data`;

            $.ajax({
                url: apiUrl,
                method: "GET",
                success: function (data) {

                    $('#total-tasks').text(data.project_overview.total_tasks);
                    $('#completed-tasks').text(data.project_overview.completed_tasks);
                    $('#pending-tasks').text(data.project_overview.pending_tasks);
                    $('#overdue-tasks').text(data.project_overview.overdue_tasks);

                    const progressPercentage = parseFloat(data.progress_percentage).toFixed(2);
                    $('#progress-percentage').text(progressPercentage);

                    new Chart(document.getElementById("progressChart").getContext("2d"), {
                        type: "pie",
                        data: {
                            labels: ["Completed", "In Progress"],
                            datasets: [{
                                label: "Progress",
                                data: [data.project_overview.completed_tasks, data.project_overview.pending_tasks],
                                backgroundColor: ["#28a745", "#ffc107"],
                            }],
                        },
                        options: { responsive: true },
                    });

                    new Chart(document.getElementById("teamActivityChart").getContext("2d"), {
                        type: "bar",
                        data: {
                            labels: data.team_activity.map(user => user.name),
                            datasets: [{
                                label: "Tasks Assigned",
                                data: data.team_activity.map(user => user.tasks),
                                backgroundColor: ["#007bff", "#6c757d", "#17a2b8"],
                            }],
                        },
                        options: { responsive: true },
                    });

                    new Chart(document.getElementById("timeSpentChart").getContext("2d"), {
                        type: "bar",
                        data: {
                            labels: data.time_spent.map(user => user.name),
                            datasets: [{
                                label: "Time Spent (HH:MM:SS)",
                                data: data.time_spent.map(user => timeToSeconds(user.time)),
                                backgroundColor: ["#007bff", "#6c757d", "#17a2b8"],
                            }],
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            const label = context.label || '';
                                            const value = context.raw || 0;
                                            const formattedTime = new Date(value * 1000).toISOString().substr(11, 8);
                                            return `${label}: ${formattedTime}`;
                                        },
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function (value) {
                                            const formattedTime = new Date(value * 1000).toISOString().substr(11, 8);
                                            return formattedTime;
                                        },
                                    },
                                },
                            },
                        },
                    });

                    new Chart(document.getElementById("overtimeHoursChart").getContext("2d"), {
                        type: "bar",
                        data: {
                            labels: data.overtime_hours.map(user => user.name),
                            datasets: [{
                                label: "Overtime Hours",
                                data: data.overtime_hours.map(user => user.hours),
                                backgroundColor: ["#dc3545", "#ffc107", "#28a745"],
                            }],
                        },
                        options: { responsive: true },
                    });

                    generateAIRecommendations(data.project_data);
                    populateTaskDetails(data.task_details);
                },
                error: function (error) {
                    console.error("Error fetching data:", error);
                    alert("Failed to load data. Please try again later.");
                },
            });

            function cleanAIResponse(text) {
                return text.replace(/[\[\]{}"]/g, '').replace(/:/g, ': ').trim();
            }

            async function generateAIRecommendations(projectData) {
                const aiRecommendationsList = $('#ai-recommendations');
                const aiLoading = $('#ai-loading');

                aiLoading.show();
                aiRecommendationsList.hide();

                const prompt = `I have data,
                    Total Tasks: ${projectData.total_tasks}, Completed Tasks: ${projectData.completed_tasks}, In Progress Tasks: ${projectData.pending_tasks}, Overdue Tasks: ${projectData.overdue_tasks}, Progress Percentage: ${projectData.progress_percentage}%, Time Spent: ${JSON.stringify(projectData.time_spent)}, Overtime Hours: ${JSON.stringify(projectData.overtime_hours)}, please provide suggestions to improve productivity from the data.
                `;

                try {
                    const response = await fetch("http://localhost/AUP_ERP3.2/api/generate", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({ prompt }),
                    });

                    const result = await response.json();

                    if (result.error) {
                        console.error("AI API Error:", result.error);
                        aiRecommendationsList.append($('<li>').addClass('list-group-item text-danger').text("Error generating recommendations."));
                        return;
                    }

                    let rawRecommendations = result[0]?.generated_text || "";

                    rawRecommendations = rawRecommendations.replace(
                        /I have data.*?please provide suggestions to improve productivity from the data\./s,
                        ''
                    );

                    const recommendations = rawRecommendations.split("\n")
                    .map(line => line.trim())
                    .filter(line => line !== "")
                    .map(cleanAIResponse);

                    if (recommendations.length > 0) {
                        aiLoading.hide();
                        aiRecommendationsList.empty().show();
                        displayRecommendationsSequentially(aiRecommendationsList, recommendations);
                    } else {
                        aiRecommendationsList.append($('<li>').addClass('list-group-item text-danger').text("No recommendations generated."));
                    }
                } catch (error) {
                    console.error("Error generating AI recommendations:", error);
                    aiRecommendationsList.append($('<li>').addClass('list-group-item text-danger').text("Failed to generate recommendations. Please try again later."));
                }
                finally {
                    aiLoading.hide();
                    aiRecommendationsList.show();
            }
            }
        });

        function timeToSeconds(time) {
            const [hours, minutes, seconds] = time.split(':').map(Number);
            return hours * 3600 + minutes * 60 + seconds;
        }

        function populateTaskDetails(taskDetails) {
            const container = $('#task-details-container');
            container.empty();

            taskDetails.forEach(user => {
                const accordionId = `accordion-${user.name.replace(/\s+/g, '-')}`;

                const card = `
                    <div class="accordion" id="${accordionId}">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-${accordionId}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${accordionId}" aria-expanded="false" aria-controls="collapse-${accordionId}">
                                    ${user.name} (${user.tasks.length} tasks)
                                </button>
                            </h2>
                            <div id="collapse-${accordionId}" class="accordion-collapse collapse" aria-labelledby="heading-${accordionId}" data-bs-parent="#${accordionId}">
                                <div class="accordion-body">
                                    <ul class="list-group list-group-flush">
                                        ${user.tasks.map(task => `
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                ${task.name}
                                                <span class="badge ${getTaskStatusBadge(task.status)} rounded-pill">${task.status}</span>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <br>
                    </div>
                `;
                container.append(card);
            });
        }

        function getTaskStatusBadge(status) {
            switch (status.toLowerCase()) {
                case 'completed':
                    return 'bg-success';
                case 'pending':
                    return 'bg-warning';
                case 'overdue':
                    return 'bg-danger';
                default:
                    return 'bg-secondary';
            }
        }

        function displayRecommendationsSequentially(container, recommendations, index = 0) {
            if (index >= recommendations.length) {
                return;
            }

            const recommendation = recommendations[index];
            const listItem = $('<li>').addClass('list-group-item');
            container.append(listItem);

            typeWriterEffect(listItem, recommendation, () => {

                displayRecommendationsSequentially(container, recommendations, index + 1);
            }, 20);
        }

        function typeWriterEffect(element, text, callback, speed = 20) {
            let i = 0;
            const interval = setInterval(() => {
                if (i < text.length) {
                    element.text(text.substring(0, i + 1));
                    i++;
                } else {
                    clearInterval(interval);
                    if (callback) callback();
                }
            }, speed);
        }

        document.addEventListener("DOMContentLoaded", function () {
            const userFilterDropdown = document.getElementById("user-filter-dropdown");
            const commentList = document.getElementById("comment-list");

            function fetchUsers() {
                fetch('{{ route('get.project.users', ['projectId' => $project->id]) }}')
                    .then(response => response.json())
                    .then(users => {

                        const showAllOption = userFilterDropdown.querySelector(".filter-show-all-users");
                        userFilterDropdown.innerHTML = "";
                        userFilterDropdown.appendChild(showAllOption);

                        users.forEach(user => {
                            const userOption = document.createElement("label");
                            userOption.classList.add("dropdown-item", "pl-4", "d-flex", "align-items-center");

                            const checkbox = document.createElement("input");
                            checkbox.type = "checkbox";
                            checkbox.classList.add("mr-2");
                            checkbox.value = user.id;

                            const userName = document.createElement("span");
                            userName.textContent = user.name;

                            userOption.appendChild(checkbox);
                            userOption.appendChild(userName);

                            checkbox.addEventListener("change", function () {
                                const selectedUsers = Array.from(userFilterDropdown.querySelectorAll("input[type='checkbox']:checked"))
                                    .map(checkbox => checkbox.value);

                                fetchComments(selectedUsers);
                            });

                            userFilterDropdown.appendChild(userOption);
                        });
                    });
            }

            function fetchComments(selectedUserIds = []) {
                fetch('{{ route('get.project.comment', ['projectId' => $project->id]) }}')
                    .then(response => response.json())
                    .then(comments => {
                        commentList.innerHTML = "";

                        comments.forEach(comment => {

                            if (selectedUserIds.length === 0 || selectedUserIds.includes(comment.user.id.toString())) {
                                const commentDiv = document.createElement("div");
                                commentDiv.classList.add("comment-item");

                                const header = document.createElement("div");
                                header.classList.add("comment-header");

                                const avatar = document.createElement("img");
                                avatar.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(comment.user.name)}&background=random`;
                                avatar.alt = comment.user.name;
                                header.appendChild(avatar);

                                const userInfo = document.createElement("div");
                                userInfo.classList.add("user-info");
                                userInfo.textContent = comment.user.name;
                                header.appendChild(userInfo);

                                const timestamp = document.createElement("div");
                                timestamp.classList.add("timestamp");
                                timestamp.textContent = new Date(comment.created_at).toLocaleString();
                                header.appendChild(timestamp);

                                commentDiv.appendChild(header);

                                if (comment.text) {
                                    const body = document.createElement("div");
                                    body.classList.add("comment-body");
                                    body.textContent = comment.text;
                                    commentDiv.appendChild(body);
                                }

                                if (comment.file_path) {
                                    const fileLink = document.createElement("a");
                                    fileLink.classList.add("comment-file");
                                    fileLink.href = comment.file_path;
                                    fileLink.target = "_blank";
                                    fileLink.textContent = "View File";
                                    commentDiv.appendChild(fileLink);
                                }

                                commentList.appendChild(commentDiv);
                            }
                        });
                    });
            }

            const showAllOption = userFilterDropdown.querySelector(".filter-show-all-users");
            showAllOption.addEventListener("click", function (e) {
                e.preventDefault();

                userFilterDropdown.querySelectorAll("input[type='checkbox']").forEach(checkbox => {
                    checkbox.checked = false;
                });

                fetchComments();
            });

            fetchUsers();
            fetchComments();
        });

        document.addEventListener("DOMContentLoaded", function () {
            const projectId = {{ $project->id }};

            async function fetchPlannings() {
                try {
                    const response = await fetch('{{ route('get.project.planning', ['projectId' => $project->id]) }}');
                    if (!response.ok) {
                        throw new Error('Failed to fetch plannings');
                    }
                    const plannings = await response.json();
                    return plannings || [];
                } catch (error) {
                    console.error('Error fetching plannings:', error);
                    return [];
                }
            }

            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) {
                console.error("Calendar element not found!");
                return;
            }

            let calendar;

            function initializeCalendar(plannings) {
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    selectable: true,
                    editable: true,
                    themeSystem: 'bootstrap',
                    events: plannings.map(planning => ({
                        id: planning.id,
                        title: planning.title,
                        start: planning.start,
                        end: planning.end,
                        allDay: true,
                        backgroundColor: stringToColor(planning.user.name),
                        borderColor: stringToColor(planning.user.name),
                        extendedProps: {
                            user: planning.user,
                            description: planning.description,
                        },
                    })),
                    eventClick: function (info) {
                        info.jsEvent.preventDefault();
                        info.jsEvent.stopPropagation();

                        document.getElementById('planning-user-detail').textContent = info.event.extendedProps.user.name;
                        document.getElementById('planning-date-detail').textContent = `From ${info.event.startStr} to ${info.event.endStr}`;
                        document.getElementById('planning-title-detail').textContent = info.event.title;

                        const detailModal = new bootstrap.Modal(document.getElementById('planningDetailModal'));
                        detailModal.show();
                    }
                });

                calendar.render();
            }

            document.getElementById('save-planning').addEventListener('click', async function () {
                const title = document.getElementById('planning-title').value.trim();
                const startDate = document.getElementById('planning-start-date').value;
                const endDate = document.getElementById('planning-end-date').value;

                if (!title || !startDate) {
                    alert('Title and Start Date are required!');
                    return;
                }

                try {
                    await fetch('{{ route('project.planning') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            project_id: projectId,
                            title: title,
                            start_date: startDate,
                            end_date: endDate || null,
                        }),
                    });

                    show_toastr('{{__('success')}}', '{{ __("Planning Added Successfully!")}}');

                    const modal = bootstrap.Modal.getInstance(document.getElementById('planningModal'));
                    modal.hide();

                    document.getElementById('planning-form').reset();

                    refreshCalendarAndList();
                } catch (error) {
                    console.error('Error saving planning:', error);
                    alert('Failed to save planning. Please try again.');
                }
            });

            async function refreshCalendarAndList() {
                const plannings = await fetchPlannings();
                calendar.removeAllEvents();
                calendar.addEventSource(plannings.map(planning => ({
                    id: planning.id,
                    title: planning.title,
                    start: planning.start,
                    end: planning.end,
                    allDay: true,
                    backgroundColor: stringToColor(planning.user.name),
                    borderColor: stringToColor(planning.user.name),
                    extendedProps: {
                        user: planning.user,
                    },
                })));
                renderPlanningList(plannings);
            }

            function stringToColor(str) {
                let hash = 0;
                for (let i = 0; i < str.length; i++) {
                    hash = str.charCodeAt(i) + ((hash << 5) - hash);
                }
                let color = '#';
                for (let i = 0; i < 3; i++) {
                    const value = (hash >> (i * 8)) & 0xff;
                    color += ('00' + value.toString(16)).substr(-2);
                }
                return color;
            }

            function renderPlanningList(plannings) {
                const planningList = document.getElementById('planning-list');
                planningList.innerHTML = '';
                const groupedPlannings = {};

                plannings.forEach(planning => {
                    if (!groupedPlannings[planning.user.name]) {
                        groupedPlannings[planning.user.name] = [];
                    }
                    groupedPlannings[planning.user.name].push(planning);
                });

                for (const [userName, userPlannings] of Object.entries(groupedPlannings)) {
                    const userDiv = document.createElement('div');
                    userDiv.innerHTML = `
                        <div class="mb-3">
                            <strong>${userName}</strong>
                            <ul class="list-unstyled mt-2">
                                ${userPlannings.map(p => `
                                    <li id="planning-${p.id}" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                        <div>
                                            <small>${p.title} - ${p.start}</small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-danger delete-planning" data-id="${p.id}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning set-reminder" data-id="${p.id}">
                                                <i class="ti ti-alarm"></i>
                                            </button>
                                        </div>
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    `;
                    planningList.appendChild(userDiv);
                }


                document.querySelectorAll('.delete-planning').forEach(button => {
                    button.addEventListener('click', async function () {
                        const planningId = this.dataset.id;
                        try {
                             await fetch(`${baseUrl}/plannings/${planningId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                },
                            });
                            refreshCalendarAndList();
                            show_toastr('{{__('success')}}', '{{ __("Delete Planning Successfully!")}}');
                        } catch (error) {
                            console.error('Error deleting planning:', error);
                            alert('Failed to delete planning. Please try again.');
                        }
                    });
                });

                document.querySelectorAll('.set-reminder').forEach(button => {
                    button.addEventListener('click', function () {
                        const planningId = this.dataset.id;
                        document.getElementById('reminder-planning-id').value = planningId;
                        const modal = new bootstrap.Modal(document.getElementById('reminderModal'));
                        modal.show();
                    });
                });

                document.getElementById('save-reminder').addEventListener('click', async function () {
                    const planningId = document.getElementById('reminder-planning-id').value;
                    const reminderTime = document.getElementById('reminder-date').value;

                    if (!reminderTime) {
                        alert('Please select a valid date and time!');
                        return;
                    }

                    try {
                        await fetch('{{ route('planning.reminders') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify({
                                planning_id: planningId,
                                reminder_time: reminderTime,
                            }),
                        });

                        show_toastr('{{__('success')}}', '{{ __("Reminder Added Successfully!")}}');

                        const modal = bootstrap.Modal.getInstance(document.getElementById('reminderModal'));
                        modal.hide();

                        document.getElementById('reminder-form').reset();
                    } catch (error) {
                        console.error('Error setting reminder:', error);
                        alert('Failed to set reminder. Please try again.');
                    }
                });
            }

            const planningTab = document.querySelector('#planning-tab');
            planningTab.addEventListener('shown.bs.tab', async function () {
                const plannings = await fetchPlannings();
                initializeCalendar(plannings);
                renderPlanningList(plannings);
            });
        });


    </script>
    <script>

        $(document).on('click', '.view-images', function () {

                var p_url = "{{route('el.image.view')}}";
                var data = {
                    'id': $(this).attr('data-id')
                };
                    postAjax(p_url, data, function (res) {
                        $('.image_sider_div').html(res);
                        $('#exampleModalCenter').modal('show');
                    });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const dropzone = document.getElementById('dropzone');
            const fileInput = document.getElementById('fileInput');
            const fileList = document.getElementById('fileList');

            dropzone.addEventListener('click', () => fileInput.click());

            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('bg-light');
            });

            dropzone.addEventListener('dragleave', () => dropzone.classList.remove('bg-light'));

            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('bg-light');
                const files = e.dataTransfer.files;
                uploadFiles(files);
            });

            fileInput.addEventListener('change', (e) => {
                const files = e.target.files;
                uploadFiles(files);
            });

            function uploadFiles(files) {
                const formData = new FormData();
                Array.from(files).forEach((file) => formData.append('file', file));

                fetch('{{ route('comment.store.file', ['id' => $project->id]) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        const listItem = document.createElement('li');
                        listItem.className = 'list-group-item px-0';
                        listItem.innerHTML = `
                            <div class="row align-items-center justify-content-between">
                                <div class="col mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="div">
                                            <h6 class="m-0">${data.name}</h6>
                                            <small class="text-muted">${data.file_size}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-sm-end d-flex align-items-center">
                                    <div class="action-btn bg-info ms-2">
                                        <a href="{{ asset(Storage::url('tasks/')) }}/${data.file}" data-bs-toggle="tooltip" title="{{__('Download')}}" class="btn btn-sm" download>
                                            <i class="ti ti-download text-white"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                        fileList.appendChild(listItem);
                    }
                })
                .catch(err => console.error(err));
            }
        });

    </script>
@endpush
@push('script-page')
    <script>
        (function () {
            var options = {
                chart: {
                    type: 'area',
                    height: 60,
                    sparkline: {
                        enabled: true,
                    },
                },
                colors: ["#ffa21d"],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2,
                },
                series: [{
                    name: 'Bandwidth',
                    data:{{ json_encode(array_map('intval',$project_data['timesheet_chart']['chart'])) }}
                }],

                tooltip: {
                    followCursor: false,
                    fixed: {
                        enabled: false
                    },
                    x: {
                        show: false
                    },
                    y: {
                        title: {
                            formatter: function (seriesName) {
                                return ''
                            }
                        }
                    },
                    marker: {
                        show: false
                    }
                }
            }
            var chart = new ApexCharts(document.querySelector("#timesheet_chart"), options);
            chart.render();
        })();

        (function () {
            var options = {
                chart: {
                    type: 'area',
                    height: 60,
                    sparkline: {
                        enabled: true,
                    },
                },
                colors: ["#ffa21d"],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2,
                },
                series: [{
                    name: 'Bandwidth',
                    data:{{ json_encode($project_data['task_chart']['chart']) }}
                }],

                tooltip: {
                    followCursor: false,
                    fixed: {
                        enabled: false
                    },
                    x: {
                        show: false
                    },
                    y: {
                        title: {
                            formatter: function (seriesName) {
                                return ''
                            }
                        }
                    },
                    marker: {
                        show: false
                    }
                }
            }
            var chart = new ApexCharts(document.querySelector("#task_chart"), options);
            chart.render();
        })();

        $(document).ready(function () {
            loadProjectUser();
            $(document).on('click', '.invite_usr', function () {
                var project_id = $('#project_id').val();
                var user_id = $(this).attr('data-id');

                $.ajax({
                    url: '{{ route('invite.project.user.member') }}',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        'project_id': project_id,
                        'user_id': user_id,
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function (data) {
                        if (data.code == '200') {
                            show_toastr(data.status, data.success, 'success')
                            setInterval('location.reload()', 5000);
                            loadProjectUser();
                        } else if (data.code == '404') {
                            show_toastr(data.status, data.errors, 'error')
                        }
                    }
                });
            });
        });

        $(document).ready(function () {
            loadProjectClient();
            $(document).on('click', '.invite_client', function () {
                var project_id = $('#project_id').val();
                var user_id = $(this).attr('data-id');

                $.ajax({
                    url: '{{ route('invite.project.client.member') }}',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        'project_id': project_id,
                        'user_id': user_id,
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function (data) {
                        if (data.code == '200') {
                            show_toastr(data.status, data.success, 'success')
                            setInterval('location.reload()', 5000);
                            loadProjectClient();
                        } else if (data.code == '404') {
                            show_toastr(data.status, data.errors, 'error')
                        }
                    }
                });
            });
        });

        function loadProjectUser() {
            var mainEle = $('#project_users');
            var project_id = '{{$project->id}}';

            $.ajax({
                url: '{{ route('project.user') }}',
                data: {project_id: project_id},
                beforeSend: function () {
                    $('#project_users').html('<tr><th colspan="2" class="h6 text-center pt-5">{{__('Loading...')}}</th></tr>');
                },
                success: function (data) {
                    mainEle.html(data.html);
                    $('[id^=fire-modal]').remove();
                    //loadConfirm();
                }
            });
        }

        function loadProjectClient() {
            var mainEle = $('#project_client');
            var project_id = '{{$project->id}}';

            $.ajax({
                url: '{{ route('project.client') }}',
                data: {project_id: project_id},
                beforeSend: function () {
                    $('#project_client').html('<tr><th colspan="2" class="h6 text-center pt-5">{{__('Loading...')}}</th></tr>');
                },
                success: function (data) {
                    mainEle.html(data.html);
                    $('[id^=fire-modal]').remove();
                    //loadConfirm();
                }
            });
        }

    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item">{{ucwords($project->project_name)}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{-- @can('view grant chart')
            <a href="{{ route('projects.gantt',$project->id) }}" class="btn btn-sm btn-primary">
                {{__('Gantt Chart')}}
            </a>
        @endcan --}}
        @if(\Auth::user()->type!='client' || (\Auth::user()->type=='client' ))
            <a href="{{ route('projecttime.tracker',$project->id) }}" class="btn btn-sm btn-primary">
                {{__('Tracker')}}
            </a>
        @endif
        {{-- @can('view expense')
            <a href="{{ route('projects.expenses.index',$project->id) }}" class="btn btn-sm btn-primary">
                {{__('Expense')}}
            </a>
        @endcan --}}
        @can('manage invoice')
            <a href="{{ route('projects.invoice',$project->id) }}" class="btn btn-sm btn-primary">
                {{__('Invoice')}}
            </a>
        @endcan
        @if(\Auth::user()->type != 'client')
            @can('view timesheet')
                <a href="{{ route('project.timesheet',$project->id) }}" class="btn btn-sm btn-primary">
                    {{__('Timesheet')}}
                </a>
            @endcan
        @endif
        <!-- @can('manage bug report')
            <a href="{{ route('task.bug',$project->id) }}" class="btn btn-sm btn-primary">
                {{__('Bug Report')}}
            </a>
        @endcan -->
        @if (\Auth::user()->type != 'client' && \Auth::user()->type != 'staff_client')
            @can('edit project task')
                <a href="{{ route('projects.tasks.index',$project->id) }}" class="btn btn-sm btn-primary">
                    {{__('Task')}}
                </a>
            @endcan
        @endif
        
        @can('edit project')
            <a href="#" data-size="lg" data-url="{{ route('projects.edit', $project->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Edit Project')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-pencil"></i>
            </a>
        @endcan

    </div>
@endsection

@section('content')

    <div class="container mt-4">
        <ul class="nav nav-tabs" id="projectTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="true">Dashboard</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments" type="button" role="tab" aria-controls="comments" aria-selected="false">Communication</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="planning-tab" data-bs-toggle="tab" data-bs-target="#planning" type="button" role="tab" aria-controls="planning" aria-selected="false">Planning</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab" aria-controls="notes" aria-selected="false">Notes</button>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="reports-tab" data-bs-toggle="tab" href="#reports" role="tab">{{__('Reports')}}</a>
            </li>
        </ul>
        <div class="tab-content" id="projectTabsContent">
            <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                <div class="row mt-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center">
                                            <div class="theme-avtar bg-success">
                                                <i class="ti ti-checks"></i>
                                            </div>
                                            <div class="ms-3">
                                                <small class="text-muted h6">{{__('Active Task')}}</small>
                                                <h6 class="m-0">{{$project_data['task']['active_tasks'] }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center">
                                            <div class="theme-avtar bg-danger">
                                                <i class="ti ti-alert-triangle"></i>
                                            </div>
                                            <div class="ms-3">
                                                <small class="text-muted h6">{{__('Overdue Task')}}</small>
                                                <h6 class="m-0">{{$project_data['task']['overdue_tasks'] }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center">
                                            <div class="theme-avtar bg-info">
                                                <i class="ti ti-clipboard-check"></i>
                                            </div>
                                            <div class="ms-3">
                                                <small class="text-muted h6">{{__('Total Task')}}</small>
                                                <h6 class="m-0">{{$project_data['task']['total'] }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center">
                                            <div class="theme-avtar bg-success">
                                                <i class="ti ti-check"></i>
                                            </div>
                                            <div class="ms-3">
                                                <small class="text-muted h6">{{__('Done Task')}}</small>
                                                <h6 class="m-0">{{$project_data['task']['percentage'] . '%'}}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <img {{ $project->img_image }} alt="" class="img-user wid-45 rounded-circle">
                                    </div>
                                    <div class="d-block  align-items-center justify-content-between w-100">
                                        <div class="mb-3 mb-sm-0">
                                            <h5 class="mb-1"> {{$project->project_name}}</h5>
                                            <p class="mb-0 text-sm">
                                                <div class="progress-wrapper">
                                                    <span class="progress-percentage"><small class="font-weight-bold">{{__('Completed:')}} : </small>{{ $project->project_progress()['percentage'] }}</span>
                                                    <div class="progress progress-xs mt-2">
                                                        <div class="progress-bar bg-info" role="progressbar" aria-valuenow="{{ $project->project_progress()['percentage'] }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $project->project_progress()['percentage'] }};"></div>
                                                    </div>
                                                </div>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <h4 class="mt-3 mb-1"></h4>
                                        <p> {{$project->description }}</p>
                                    </div>
                                </div>
                                <div class="card bg-primary mb-0">
                                    <div class="card-body">
                                        <div class="d-block d-sm-flex align-items-center justify-content-between">
                                            <div class="row align-items-center">
                                                <span class="text-white text-sm">{{__('Start Date')}}</span>
                                                <h5 class="text-white text-nowrap">{{ Utility::getDateFormated($project->start_date) }}</h5>
                                            </div>
                                            <div class="row align-items-center">
                                                <span class="text-white text-sm">{{__('End Date')}}</span>
                                                <h5 class="text-white text-nowrap">{{ Utility::getDateFormated($project->end_date) }}</h5>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <span class="text-white text-sm">{{__('Client')}}</span>
                                            <h5 class="text-white text-nowrap">{{ (!empty($project->client)?$project->client->name: 'No Client') }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-clipboard-list"></i>
                                    </div>
                                    <div class="ms-3">
                                        <p class="text-muted mb-0">{{__('Last 7 days task done')}}</p>
                                        <h4 class="mb-0">{{ $project_data['task_chart']['total'] }}</h4>

                                    </div>
                                </div>
                                <div id="task_chart"></div>
                            </div>

                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted">{{__('Day Left')}}</span>
                                    </div>
                                    <span>{{ $project_data['day_left']['day'] }}</span>
                                </div>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-primary" style="width: {{ $project_data['day_left']['percentage'] }}%"></div>
                                </div>
                                {{-- <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center">

                                        <span class="text-muted">{{__('Open Task')}}</span>
                                    </div>
                                    <span>{{ $project_data['open_task']['tasks'] }}</span>
                                </div>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-primary" style="width: {{ $project_data['open_task']['percentage'] }}%"></div>
                                </div> --}}
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted">{{__('Completed Milestone')}}</span>
                                    </div>
                                    <span>{{ $project_data['milestone']['total'] }}</span>
                                </div>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-primary" style="width: {{ $project_data['milestone']['percentage'] }}%"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-clipboard-list"></i>
                                    </div>
                                    <div class="ms-3">
                                        <p class="text-muted mb-0">{{__('Last 7 days hours spent')}}</p>
                                        <h4 class="mb-0">{{ $project_data['timesheet_chart']['total'] }}</h4>

                                    </div>
                                </div>
                                <div id="timesheet_chart"></div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted">{{__('Total project time spent')}}</span>
                                    </div>
                                    <span>{{ $project_data['time_spent']['total'] }}</span>
                                </div>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-primary" style="width: {{ $project_data['time_spent']['percentage'] }}%"></div>
                                </div>
                                {{-- <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center">

                                        <span class="text-muted">{{__('Allocated hours on task')}}</span>
                                    </div>
                                    <span>{{ $project_data['task_allocated_hrs']['hrs'] }}</span>
                                </div>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-primary" style="width: {{ $project_data['task_allocated_hrs']['percentage'] }}%"></div>
                                </div> --}}
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted">{{__('Total Member')}}</span>
                                    </div>
                                    <span>{{ $project_data['user_assigned']['total'] }}</span>
                                </div>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-primary" style="width: {{ $project_data['user_assigned']['percentage'] }}%"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5>{{__('Members')}}</h5>
                                    @can('edit project')
                                    @if(Auth::user()->type != "client")
                                        <div class="float-end">
                                            <a href="#" data-size="lg" data-url="{{ route('invite.project.member.view', $project->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary" data-bs-original-title="{{__('Add Member')}}">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                    @endif
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush list" id="project_users">
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="card  activity-scroll">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5>{{__('Task Group / Milestones')}} ({{count($project->milestones)}})</h5>
                                    @can('create milestone')
                                        <div class="float-end">
                                            <a href="#" data-size="md" data-url="{{ route('project.milestone', $project->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary" data-bs-original-title="{{__('Create Task Group / Milestone')}}">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body vertical-scroll-cards">
                                <ul class="list-group list-group-flush">
                                    @if($project->milestones->count() > 0)
                                        @foreach($project->milestones as $milestone)
                                            <li class="list-group-item px-0">
                                                <div class="row align-items-center justify-content-between">
                                                    <div class="col-sm-auto mb-3 mb-sm-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="div">
                                                                <h6 class="m-0">{{ $milestone->title }}
                                                                    <span class="badge-xs badge bg-{{\App\Models\Project::$status_color[$milestone->status]}} p-2 px-3 rounded">{{ __(\App\Models\Project::$project_status[$milestone->status]) }}</span>
                                                                </h6>
                                                                <small class="text-muted">{{ $milestone->tasks->count().' '. __('Tasks') }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-auto text-sm-end d-flex align-items-center">
                                                        @can('view milestone')
                                                            <div class="action-btn bg-warning ms-2">
                                                                <a href="#" data-size="lg" data-url="{{ route('project.milestone.show',$milestone->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('View')}}" class="btn btn-sm">
                                                                    <i class="ti ti-eye text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('edit milestone')
                                                            <div class="action-btn bg-info ms-2">
                                                                <a href="#" data-size="md" data-url="{{ route('project.milestone.edit',$milestone->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Milestone')}}"class="btn btn-sm">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('delete milestone')
                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['project.milestone.destroy', $milestone->id]]) !!}
                                                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    @else
                                        <div class="py-5">
                                            <h6 class="h6 text-center">{{__('No Milestone Found.')}}</h6>
                                        </div>
                                    @endif
                                </ul>

                            </div>
                        </div>
                    </div>
                    @if(Auth::user()->type == "admin" || Auth::user()->type == "company")
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ __('Project Offering') }}</h5>
                                </div>
                                <div class="card-body" style="min-height: 280px;">
                                    <div class="row">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Position') }}</th>
                                                    <th>{{ __('Project Hours') }}</th>
                                                    <th>{{ __('Total Charge Out') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @if(isset($project_offerings) && !empty($project_offerings) > 0)
                                                <tr>
                                                    <td>{{ __('Partners') }}</td>
                                                    <td>{{ $project_offerings->als_partners ? $project_offerings->als_partners . ' H' : __('No Data Available') }}</td>
                                                    <td>{{ $project_offerings->als_partners && $project_offerings->rate_partners ? \Auth::user()->priceFormat($project_offerings->als_partners * $project_offerings->rate_partners) : __('No Data Available') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ __('Manager') }}</td>
                                                    <td>{{ $project_offerings->als_manager ? $project_offerings->als_manager . ' H' : __('No Data Available') }}</td>
                                                    <td>{{ $project_offerings->als_manager && $project_offerings->rate_manager ? \Auth::user()->priceFormat($project_offerings->als_manager * $project_offerings->rate_manager) : __('No Data Available') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ __('Leader') }}</td>
                                                    <td>{{ $project_offerings->als_leader ? $project_offerings->als_leader . ' H' : __('No Data Available') }}</td>
                                                    <td>{{ $project_offerings->als_leader && $project_offerings->rate_leader ? \Auth::user()->priceFormat($project_offerings->als_leader * $project_offerings->rate_leader) : __('No Data Available') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ __('Senior Associate') }}</td>
                                                    <td>{{ $project_offerings->als_senior_associate ? $project_offerings->als_senior_associate . ' H' : __('No Data Available') }}</td>
                                                    <td>{{ $project_offerings->als_senior_associate && $project_offerings->rate_senior_associate ? \Auth::user()->priceFormat($project_offerings->als_senior_associate * $project_offerings->rate_senior_associate) : __('No Data Available') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ __('Associate') }}</td>
                                                    <td>{{ $project_offerings->als_associate ? $project_offerings->als_associate . ' H' : __('No Data Available') }}</td>
                                                    <td>{{ $project_offerings->als_associate && $project_offerings->rate_associate ? \Auth::user()->priceFormat($project_offerings->als_associate * $project_offerings->rate_associate) : __('No Data Available') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ __('Assistant') }}</td>
                                                    <td>{{ $project_offerings->als_intern ? $project_offerings->als_intern . ' H' : __('No Data Available') }}</td>
                                                    <td>{{ $project_offerings->als_intern && $project_offerings->rate_intern ? \Auth::user()->priceFormat($project_offerings->als_intern * $project_offerings->rate_intern) : __('No Data Available') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>{{ __('Total') }}</strong></td>
                                                    <td><strong>{{ ($project_offerings->als_partners + $project_offerings->als_manager + $project_offerings->als_senior_associate + $project_offerings->als_associate + $project_offerings->als_intern) ? 
                                                        ($project_offerings->als_partners + $project_offerings->als_manager + $project_offerings->als_senior_associate + $project_offerings->als_associate + $project_offerings->als_intern) . ' H' : __('No Data Available') }}
                                                    </strong></td>
                                                    <td><strong>{{ ($project_offerings->als_partners * $project_offerings->rate_partners + 
                                                        $project_offerings->als_manager * $project_offerings->rate_manager + 
                                                        $project_offerings->als_leader * $project_offerings->rate_leader + 
                                                        $project_offerings->als_senior_associate * $project_offerings->rate_senior_associate + 
                                                        $project_offerings->als_associate * $project_offerings->rate_associate + 
                                                        $project_offerings->als_intern * $project_offerings->rate_intern) ? 
                                                        \Auth::user()->priceFormat(
                                                        $project_offerings->als_partners * $project_offerings->rate_partners + 
                                                        $project_offerings->als_manager * $project_offerings->rate_manager + 
                                                        $project_offerings->als_leader * $project_offerings->rate_leader + 
                                                        $project_offerings->als_senior_associate * $project_offerings->rate_senior_associate + 
                                                        $project_offerings->als_associate * $project_offerings->rate_associate + 
                                                        $project_offerings->als_intern * $project_offerings->rate_intern
                                                        ) : __('No Data Available') }}
                                                    </strong></td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <th scope="col" colspan="7"><h6 class="text-center">{{__('No Projects Offering Found.')}}</h6></th>
                                                </tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-lg-6 col-md-6">
                        <div class="card activity-scroll">
                            <div class="card-header">
                                <h5>{{__('Attachments')}}</h5>
                                <small>{{__('Attachment that uploaded in this project')}}</small>
                            </div>
                            <div class="card-body">
                                <!-- Drag and Drop Area -->
                                <div id="dropzone" class="border border-dashed p-4 text-center mb-3">
                                    <p class="mb-0">{{__('Drag & drop files here or click to upload')}}</p>
                                    <input type="file" id="fileInput" multiple style="display:none;">
                                </div>

                                <ul class="list-group list-group-flush" id="fileList">
                                    @if($project->projectAttachments()->count() > 0)
                                        @foreach($project->projectAttachments() as $attachment)
                                            <li class="list-group-item px-0">
                                                <div class="row align-items-center justify-content-between">
                                                    <div class="col mb-3 mb-sm-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="div">
                                                                <h6 class="m-0">{{ $attachment->name }}</h6>
                                                                <small class="text-muted">{{ $attachment->file_size }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto text-sm-end d-flex align-items-center">
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="{{asset(Storage::url('tasks/'.$attachment->file))}}" data-bs-toggle="tooltip" title="{{__('Download')}}" class="btn btn-sm" download>
                                                                <i class="ti ti-download text-white"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    @else
                                        <div class="py-5">
                                            <h6 class="h6 text-center">{{__('No Attachments Found.')}}</h6>
                                        </div>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                    @if(Auth::user()->type == "admin" || Auth::user()->type == "company" || Auth::user()->type == "senior accounting" || Auth::user()->type == "senior audit" || Auth::user()->type == "manager audit" || Auth::user()->type == "partners")
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5>{{ __('Contract Data')}}</h5>
                                        @can('edit project')
                                            <div class="float-end">
                                                <a href="#" data-size="lg" data-url="{{ route('create.el.project', [$project->id, $project->client_id]) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary" data-bs-original-title="{{__('Add Contract')}}">
                                                    <i class="ti ti-plus"></i>
                                                </a>
                                            </div>
                                        @endcan
                                    </div>
                                </div>
                                <div class="card-body" style="min-height: 280px;">
                                    <div class="row">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('El Number') }}</th>
                                                    <th>{{ __('File Contract') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody> 
                                                @foreach ($el as $els)
                                                    <tr class="font-style">
                                                        <td>{{ $els->el_number}}</td>
                                                        <td>
                                                            <img alt="Image placeholder" src="{{ asset('assets/images/gallery.png')}}" class="avatar view-images rounded-circle avatar-sm" data-bs-toggle="tooltip" title="{{__('View File')}}" data-original-title="{{__('View File')}}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="{{$els->id}}" id="track-images-{{$els->id}}">
                                                        </td>
                                                        <td>
                                                            @if ($els->status == 'Draft')
                                                                <span
                                                                    class="status_badge badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\El::$status[$els->status]) }}</span>
                                                            @elseif($els->status == 'Revision')
                                                                <span
                                                                    class="status_badge badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\El::$status[$els->status]) }}</span>
                                                            @elseif($els->status == 'Latest')
                                                                <span
                                                                    class="status_badge badge bg-success p-2 px-3 rounded">{{ __(\App\Models\El::$status[$els->status]) }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="action-btn bg-primary ms-2">
                                                                <a href="#" data-url="{{ URL::to('el/'.$els->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Contract')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg ss_modale " role="document">
                                        <div class="modal-content image_sider_div">

                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @can('view activity')
                        <div class="col-xl-6">
                            <div class="card activity-scroll">
                                <div class="card-header">
                                    <h5>{{__('Activity Log')}}</h5>
                                    <small>{{__('Activity Log of this project')}}</small>
                                </div>
                                <div class="card-body vertical-scroll-cards">
                                    @foreach($project->activities as $activity)
                                        <div class="card p-2 mb-2">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div class="theme-avtar bg-primary">
                                                        <i class="ti ti-{{$activity->logIcon($activity->log_type)}}"></i>
                                                    </div>
                                                    <div class="ms-3">
                                                        <h6 class="mb-0">{{ __($activity->log_type) }}</h6>
                                                        <p class="text-muted text-sm mb-0">{!! $activity->getRemark() !!}</p>
                                                    </div>
                                                </div>
                                                <p class="text-muted text-sm mb-0">{{$activity->created_at->diffForHumans()}}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>
            </div>
            <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                <div class="d-flex justify-content-end align-items-center mb-3 mt-3">
                    <!-- Dropdown Filter -->
                    <div class="d-flex justify-content-end">
                        <div class="form-group">
                            <a href="#" class="btn btn-sm btn-primary action-item" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="btn-inner--icon"><i class="fas fa-filter" aria-hidden="true"></i></span>
                            </a>
                            <div class="dropdown-menu project-filter-actions-label dropdown-steady" id="user-filter-dropdown">
                                <a class="dropdown-item filter-action-user filter-show-all-users pl-4 active" href="#">{{__('Show All')}}</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comment List and Form -->
                <div class="mt-2">
                    <div id="comment-list" class="mb-3"></div>
                    <form id="comment-form" class="d-flex align-items-center">
                        <textarea id="comment-text" class="form-control flex-grow-1 mr-2" placeholder="Type your message..." rows="1"></textarea>
                        <label for="comment-file" id="comment-file-label" class="mr-2">
                            <i class="fas fa-paperclip"></i>
                        </label>
                        <input type="file" id="comment-file" class="d-none" accept=".jpg,.jpeg,.png,.pdf,.docx">
                        <div id="file-name-preview" class="text-muted small mt-2" style="display: none;">
                            Selected file: <span id="selected-file-name"></span>
                        </div>
                        <button type="submit" id="send-button" class="btn btn-primary btn-sm mb-2">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="planning" role="tabpanel" aria-labelledby="planning-tab">
                <div class="row mt-4">
                    <!-- Calendar Container -->
                    <div class="col-md-8">
                        <div class="card h-100">
                            <div class="card-body">
                                <div id="calendar"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Planning List -->
                    <div class="col-md-4 mt-2">
                        <div class="card h-100">
                            <div class="card-header">Planning List</div>
                            <div class="card-body" id="planning-list">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                <div class="container-fluid mt-4">

                    <!-- Two-Column Layout -->
                    <div class="row">
                        <!-- Left Column: Add Note Form -->
                        <div class="col-md-7">
                            <div class="card h-100">
                                <div class="card-header text-black">Add New Note</div>
                                <div class="card-body">
                                    <form id="add-note-form">
                                        <div class="mb-3">
                                            <textarea class="form-control" id="note-content" rows="5" placeholder="Write your note here..." required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">Add Note</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Notes List -->
                        <div class="col-md-5">
                            <div class="card h-100">
                                <div class="card-header text-black">Notes</div>
                                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                    <div id="notes-by-user-container">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="reports" role="tabpanel" aria-labelledby="reports-tab">
                <div class="container-fluid mt-4">

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header text-black">Progress Overview</div>
                                <div class="card-body">
                                    <p>Progress: <span id="progress-percentage" class="fw-bold">Loading...</span>%</p>
                                    <canvas id="progressChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header text-black">Project Overview</div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Total Tasks
                                            <span id="total-tasks" class="badge bg-primary rounded-pill">Loading...</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Completed Tasks
                                            <span id="completed-tasks" class="badge bg-success rounded-pill">Loading...</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Pending Tasks
                                            <span id="pending-tasks" class="badge bg-warning rounded-pill">Loading...</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Overdue Tasks
                                            <span id="overdue-tasks" class="badge bg-danger rounded-pill">Loading...</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">Team Activity</div>
                                <div class="card-body">
                                    <canvas id="teamActivityChart" width="800" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header text-black">Task Details per User</div>
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    <div id="task-details-container">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">Time Spent on Project (per User)</div>
                                <div class="card-body">
                                    <canvas id="timeSpentChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">Overtime Hours (per User)</div>
                                <div class="card-body">
                                    <canvas id="overtimeHoursChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">SmartAI Recommendations</div>
                                    <div class="card-body">
                                        <!-- Loading Indicator -->
                                        <div id="ai-loading" class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2">Generating recommendations...</p>
                                        </div>

                                        <!-- Recommendations List -->
                                        <ul class="list-group list-group-flush" id="ai-recommendations" style="display: none;">
                                            <!-- Recommendations will be dynamically populated here -->
                                        </ul>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="planningModal" tabindex="-1" aria-labelledby="planningModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="planningModalLabel">Add New Planning</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="planning-form">
                            <div class="mb-3">
                                <label for="planning-title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="planning-title" placeholder="Enter planning title" required>
                            </div>
                            <div class="mb-3">
                                <label for="planning-start-date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="planning-start-date" required>
                            </div>
                            <div class="mb-3">
                                <label for="planning-end-date" class="form-label">End Date (Optional)</label>
                                <input type="date" class="form-control" id="planning-end-date">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="save-planning">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="reminderModal" tabindex="-1" aria-labelledby="reminderModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reminderModalLabel">Set Reminder</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="reminder-form">
                            <input type="hidden" id="reminder-planning-id">
                            <div class="mb-3">
                                <label for="reminder-date" class="form-label">Reminder Date and Time</label>
                                <input type="datetime-local" class="form-control" id="reminder-date" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="save-reminder">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="planningDetailModal" tabindex="-1" aria-labelledby="planningDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="planningDetailModalLabel">Planning Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Employee:</strong> <span id="planning-user-detail"></span></p>
                        <p><strong>Planning Date:</strong> <span id="planning-date-detail"></span></p>
                        <p><strong>Planning:</strong> <span id="planning-title-detail"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
