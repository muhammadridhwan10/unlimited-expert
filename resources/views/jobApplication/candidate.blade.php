@extends('layouts.admin')
@section('page-title')
    {{__('Manage Job Candidates')}}
@endsection
@push('css-page')
    <style>
        /* Clean card styling - Same as template */
        .clean-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e3e6f0;
            margin-bottom: 1.5rem;
        }

        .card-header-clean {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
        }

        .card-header-clean h6 {
            margin: 0;
            font-weight: 600;
            color: #495057;
        }

        /* Live Search Bar */
        .live-search-container {
            position: relative;
            margin-bottom: 1rem;
        }

        .live-search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e3e6f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .live-search-input:focus {
            border-color: #007bff;
            background: #fff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
            outline: none;
        }

        .live-search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.25rem;
        }

        .search-results-count {
            padding: 0.5rem 1rem;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: none;
        }

        /* Summary cards */
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .summary-card.psychotest {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .summary-card.interview1 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .summary-card.interview2 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .summary-card.hired {
            background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
        }

        .summary-card.rejected {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }

        .summary-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .summary-label {
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: 500;
        }

        /* Table styling */
        .table-clean {
            margin: 0;
            background: white;
            font-size: 0.875rem;
        }

        .table-clean thead th {
            background: #f8f9fa;
            border-top: none;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            padding: 1rem 0.75rem;
            white-space: nowrap;
            vertical-align: middle;
        }

        .table-clean tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }

        .table-clean tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table-clean tbody tr.no-match {
            display: none;
        }

        /* Candidate info styling */
        .candidate-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .candidate-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .candidate-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .candidate-details h6 {
            margin: 0;
            font-weight: 600;
            color: #495057;
        }

        .candidate-details small {
            color: #6c757d;
        }

        /* Status badges */
        .stage-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            text-align: center;
            min-width: 100px;
        }

        /* Stage dropdown styling */
        .stage-select {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            border: 1px solid #90caf9;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
            min-width: 120px;
        }

        .stage-select:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.25);
        }

        .stage-select option[value="2"] {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        .stage-select option[value="3"] {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .stage-select option[value="4"] {
            background-color: #e8f5e8;
            color: #2e7d32;
        }

        .stage-select option[value="5"] {
            background-color: #e1f5fe;
            color: #0277bd;
        }

        .stage-select option[value="6"] {
            background-color: #ffebee;
            color: #c62828;
        }

        .stage-2 {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #ef6c00;
        }

        .stage-3 {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
        }

        .stage-4 {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
            color: #2e7d32;
        }

        .stage-5 {
            background: linear-gradient(135deg, #e1f5fe 0%, #b3e5fc 100%);
            color: #0277bd;
        }

        .stage-6 {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            color: #c62828;
        }

        /* Rating stars */
        .rating-stars {
            display: flex;
            gap: 2px;
            align-items: center;
        }

        .rating-stars i {
            font-size: 0.875rem;
            color: #ffc107;
        }

        .rating-stars i.empty {
            color: #dee2e6;
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .action-btn.bg-primary {
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: white;
        }

        .action-btn.bg-success {
            background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
            color: white;
        }

        .action-btn.bg-warning {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
        }

        .action-btn.bg-danger {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            color: white;
        }

        /* Info badge styling with status colors */
        .info-badge {
            background: #f8f9fa;
            color: #495057;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
        }

        .info-badge.text-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .info-badge.text-primary {
            background: linear-gradient(135deg, #cce5ff 0%, #b3d9ff 100%);
            color: #004085;
            border: 1px solid #b3d9ff;
        }

        .info-badge.text-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .info-badge.text-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
            color: #721c24;
            border: 1px solid #f1b0b7;
        }

        .info-badge.text-muted {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .info-badge i {
            font-size: 0.75rem;
        }

        .info-badge small {
            display: block;
            line-height: 1.2;
        }

        .date-badge {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #ef6c00;
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            text-align: center;
        }

        .gender-badge {
            background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
            color: #7b1fa2;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        /* Form styling */
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-select, .form-control {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            transition: border-color 0.2s ease;
        }

        .form-select:focus, .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Auto-filter styling */
        .auto-filter-select {
            transition: all 0.3s ease;
        }

        .auto-filter-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            color: #4a5568;
            margin-bottom: 0.5rem;
        }

        /* Filter grid */
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        /* Pagination styling */
        .pagination {
            justify-content: center;
            margin: 0;
        }

        .pagination .page-link {
            border: 1px solid #dee2e6;
            color: #495057;
            padding: 0.5rem 0.75rem;
            margin: 0 2px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .pagination .page-link:hover {
            background: #f8f9fa;
            border-color: #007bff;
            color: #007bff;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border-color: #007bff;
            color: white;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .summary-number {
                font-size: 1.5rem;
            }
            
            .card-header-clean {
                padding: 0.75rem 1rem;
            }

            .table-clean {
                font-size: 0.75rem;
            }

            .candidate-info {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .candidate-avatar {
                width: 35px;
                height: 35px;
            }

            .action-buttons {
                justify-content: center;
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Search highlight */
        .search-highlight {
            background-color: #ffeb3b;
            padding: 0 2px;
            border-radius: 2px;
            font-weight: 600;
        }

        /* Loading animation */
        .ti-loader.rotating {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Stage update loading */
        .stage-updating {
            opacity: 0.6;
            pointer-events: none;
        }

        .stage-updating::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endpush

@push('script-page')
    <script>
        $(document).ready(function() {
            initializeApp();
            initializeLiveSearch();
            initializeAutoFilters();
        });

        function initializeApp() {
            initializeTooltips();
            initializeSelect2();
            initializeFormHandlers();
            initializeCounterAnimation();
            animateCards();
        }

        function initializeTooltips() {
            $('[data-bs-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                placement: 'top'
            });
        }

        function initializeSelect2() {
            if ($('.select2').length) {
                $('.select2').each(function() {
                    $(this).select2({
                        placeholder: $(this).attr('placeholder') || "{{__('Select an option')}}",
                        allowClear: true,
                        width: '100%',
                        theme: 'bootstrap-5'
                    });
                });
            }
        }

        function initializeFormHandlers() {
            $('input[name="applied_from"], input[name="applied_to"]').on('change', validateDateRange);
        }

        // DataTable-style Live Search Functionality for Candidates
        function initializeLiveSearch() {
            const searchInput = $('#liveSearchInput');
            const tableRows = $('.table-clean tbody tr:not(.no-data-row)');
            const resultsCount = $('#searchResultsCount');
            const paginationContainer = $('.pagination').parent();
            
            searchInput.on('input', function() {
                const searchTerm = $(this).val().toLowerCase().trim();
                
                if (searchTerm === '') {
                    // Show all rows when search is empty
                    tableRows.show().removeClass('no-match');
                    resultsCount.hide();
                    paginationContainer.show();
                    updateSummaryCardsFromVisible();
                    return;
                }

                let visibleCount = 0;
                let visibleRows = $();

                tableRows.each(function() {
                    const row = $(this);
                    
                    // Remove previous highlights
                    row.find('.search-highlight').each(function() {
                        $(this).replaceWith($(this).text());
                    });
                    
                    // Get searchable text from all cells
                    const searchableText = row.find('td').map(function() {
                        return $(this).text().replace(/\s+/g, ' ').trim();
                    }).get().join(' ').toLowerCase();
                    
                    if (searchableText.includes(searchTerm)) {
                        row.show().removeClass('no-match');
                        visibleCount++;
                        visibleRows = visibleRows.add(row);
                        
                        // Highlight matching text in visible rows
                        highlightSearchTerm(row, searchTerm);
                    } else {
                        row.hide().addClass('no-match');
                    }
                });

                // Update results count
                if (visibleCount > 0) {
                    resultsCount.show().html(`<i class="ti ti-check-circle"></i> {{__('Found')}} <strong>${visibleCount}</strong> {{__('candidates for')}} "<strong>${searchTerm}</strong>"`);
                    paginationContainer.hide(); // Hide pagination when searching
                } else {
                    resultsCount.show().html(`<i class="ti ti-alert-circle"></i> {{__('No candidates found for')}} "<strong>${searchTerm}</strong>"`);
                    paginationContainer.hide();
                    
                    // Show "no results" message
                    if ($('.no-search-results').length === 0) {
                        $('.table-clean tbody').append(`
                            <tr class="no-search-results">
                                <td colspan="9">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="ti ti-search-off"></i>
                                        </div>
                                        <h5>{{__('No candidates found')}}</h5>
                                        <p>{{__('Try different keywords or')}} <a href="#" onclick="clearSearch()">{{__('clear search')}}</a></p>
                                    </div>
                                </td>
                            </tr>
                        `);
                    }
                }

                // Update summary cards with filtered data
                updateSummaryCardsFromVisible(visibleRows);
            });

            // Clear search function
            window.clearSearch = function() {
                searchInput.val('');
                searchInput.trigger('input');
            };

            // Add clear button to search input
            if ($('.search-clear-btn').length === 0) {
                searchInput.after(`
                    <button type="button" class="search-clear-btn" onclick="clearSearch()" style="display: none;">
                        <i class="ti ti-x"></i>
                    </button>
                `);
            }

            // Show/hide clear button
            searchInput.on('input', function() {
                const clearBtn = $('.search-clear-btn');
                if ($(this).val().trim() !== '') {
                    clearBtn.show();
                } else {
                    clearBtn.hide();
                    $('.no-search-results').remove();
                }
            });
        }

        function highlightSearchTerm(row, searchTerm) {
            const terms = searchTerm.split(/\s+/).filter(term => term.length > 1);
            
            row.find('td').each(function() {
                const cell = $(this);
                let cellHtml = cell.html();
                
                terms.forEach(term => {
                    const regex = new RegExp(`(${escapeRegExp(term)})(?![^<]*>)`, 'gi');
                    cellHtml = cellHtml.replace(regex, '<span class="search-highlight">$1</span>');
                });
                
                cell.html(cellHtml);
            });
        }

        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g),
        function initializeLiveSearch() {
            const searchInput = $('#liveSearchInput');
            const resultsCount = $('#searchResultsCount');
            const tableBody = $('.table-clean tbody');
            const originalTableContent = tableBody.html(); // Store original content
            let searchTimeout;

            searchInput.on('input', function() {
                const searchTerm = $(this).val().trim();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                if (searchTerm === '') {
                    // Restore original content when search is empty
                    tableBody.html(originalTableContent);
                    resultsCount.hide();
                    // Re-initialize tooltips for restored content
                    $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
                    return;
                }

                // Show loading state
                resultsCount.show().html('<i class="ti ti-loader rotating"></i> {{__("Searching candidates...")}}');
                
                // Debounce search to avoid too many requests
                searchTimeout = setTimeout(function() {
                    performLiveSearch(searchTerm);
                }, 500); // Wait 500ms after user stops typing
            });
        }

        function performLiveSearch(searchTerm) {
            const resultsCount = $('#searchResultsCount');
            const tableBody = $('.table-clean tbody');
            
            // Get current filters
            const currentFilters = {
                university: $('select[name="university"]').val(),
                ipk: $('select[name="ipk"]').val(),
                gender: $('select[name="gender"]').val(),
                status: $('select[name="status"]').val(),
                applied_from: $('input[name="applied_from"]').val(),
                applied_to: $('input[name="applied_to"]').val(),
                search: searchTerm
            };

            $.ajax({
                url: "{{ route('job.application.candidate') }}",
                type: "GET",
                data: currentFilters,
                beforeSend: function() {
                    tableBody.css('opacity', '0.5');
                },
                success: function(response) {
                    // Extract table content from response
                    const newTableContent = $(response).find('.table-clean tbody').html();
                    const resultsText = $(response).find('.table-clean tbody tr:not(.no-data-row)').length;
                    
                    // Update table content
                    tableBody.html(newTableContent);
                    
                    // Update results count
                    if (resultsText > 0) {
                        resultsCount.html(`<i class="ti ti-check-circle"></i> {{__('Found')}} ${resultsText} {{__('candidates for')}} "<strong>${searchTerm}</strong>"`);
                        
                        // Highlight search terms in results
                        highlightSearchTerms(searchTerm);
                        
                        // Update summary cards with new data
                        updateSummaryCardsFromResults();
                    } else {
                        resultsCount.html(`<i class="ti ti-alert-circle"></i> {{__('No candidates found for')}} "<strong>${searchTerm}</strong>"`);
                        
                        // Show empty state
                        tableBody.html(`
                            <tr class="no-data-row">
                                <td colspan="9">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="ti ti-search-off"></i>
                                        </div>
                                        <h5>{{__('No candidates found')}}</h5>
                                        <p>{{__('No candidates found for')}} "<strong>${searchTerm}</strong>"</p>
                                        <button class="btn btn-sm btn-outline-primary" onclick="clearSearch()">
                                            <i class="ti ti-x me-1"></i>{{__('Clear Search')}}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `);
                    }
                    
                    // Re-initialize tooltips
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    
                    tableBody.css('opacity', '1');
                },
                error: function(xhr, status, error) {
                    resultsCount.html(`<i class="ti ti-alert-triangle"></i> {{__('Search failed. Please try again.')}}`);
                    tableBody.css('opacity', '1');
                    console.error('Search error:', error);
                }
            });
        }

        function highlightSearchTerms(searchTerm) {
            const terms = searchTerm.toLowerCase().split(/\s+/);
            
            $('.table-clean tbody tr').each(function() {
                const $row = $(this);
                
                terms.forEach(term => {
                    if (term.length > 1) { // Only highlight terms longer than 1 character
                        $row.find('td').each(function() {
                            const $td = $(this);
                            const originalText = $td.text();
                            const regex = new RegExp(`(${term})`, 'gi');
                            const highlightedText = originalText.replace(regex, '<span class="search-highlight">$1</span>');
                            
                            if (originalText !== highlightedText) {
                                // Only replace if we found matches and if the cell doesn't contain other HTML elements
                                if ($td.children().length === 0 || $td.find('.info-badge, .date-badge, .gender-badge').length > 0) {
                                    // For simple text or badge containers
                                    $td.html(highlightedText);
                                }
                            }
                        });
                    }
                });
            });
        }

        function updateSummaryCardsFromResults() {
            const visibleRows = $('.table-clean tbody tr:not(.no-data-row)');
            if (visibleRows.length === 0) return;

            let psychotest = 0;
            let interview1 = 0;
            let interview2 = 0;
            let hired = 0;
            let rejected = 0;
            let totalCandidates = visibleRows.length;

            visibleRows.each(function() {
                const stageSelect = $(this).find('.stage-select');
                const stageValue = stageSelect.length ? stageSelect.val() : null;
                
                switch(stageValue) {
                    case '2':
                        psychotest++;
                        break;
                    case '3':
                        interview1++;
                        break;
                    case '4':
                        interview2++;
                        break;
                    case '5':
                        hired++;
                        break;
                    case '6':
                        rejected++;
                        break;
                }
            });

            // Animate counter updates
            animateCounter($('.psychotest .summary-number'), psychotest);
            animateCounter($('.interview1 .summary-number'), interview1);
            animateCounter($('.interview2 .summary-number'), interview2);
            animateCounter($('.hired .summary-number'), hired);
            animateCounter($('.rejected .summary-number'), rejected);
            animateCounter($('.summary-card:not(.psychotest):not(.interview1):not(.interview2):not(.hired):not(.rejected) .summary-number'), totalCandidates);
        }

        function clearSearch() {
            $('#liveSearchInput').val('');
            const tableBody = $('.table-clean tbody');
            
            // Restore original content
            location.reload(); // Simple way to restore original state
        }');
        }

        function updateSummaryCardsFromVisible(visibleRows = null) {
            const rows = visibleRows || $('.table-clean tbody tr:visible:not(.no-data-row):not(.no-search-results)');
            
            if (rows.length === 0) {
                // Reset to original values when no visible rows
                const originalRows = $('.table-clean tbody tr:not(.no-data-row):not(.no-search-results)');
                updateSummaryCardsWithRows(originalRows);
                return;
            }

            updateSummaryCardsWithRows(rows);
        }

        function updateSummaryCardsWithRows(rows) {
            let psychotest = 0;
            let interview1 = 0;
            let interview2 = 0;
            let hired = 0;
            let rejected = 0;
            let totalCandidates = rows.length;

            rows.each(function() {
                const stageSelect = $(this).find('.stage-select');
                if (stageSelect.length) {
                    const stageValue = stageSelect.val();
                    
                    switch(stageValue) {
                        case '2':
                            psychotest++;
                            break;
                        case '3':
                            interview1++;
                            break;
                        case '4':
                            interview2++;
                            break;
                        case '5':
                            hired++;
                            break;
                        case '6':
                            rejected++;
                            break;
                    }
                }
            });

            // Animate counter updates
            animateCounter($('.psychotest .summary-number'), psychotest);
            animateCounter($('.interview1 .summary-number'), interview1);
            animateCounter($('.interview2 .summary-number'), interview2);
            animateCounter($('.hired .summary-number'), hired);
            animateCounter($('.rejected .summary-number'), rejected);
            animateCounter($('.summary-card:not(.psychotest):not(.interview1):not(.interview2):not(.hired):not(.rejected) .summary-number'), totalCandidates);
        }

        // Auto Filters (without submit button)
        function initializeAutoFilters() {
            const filterSelects = $('.auto-filter-select');
            
            filterSelects.on('change', function() {
                setTimeout(function() {
                    $('#candidate_filter').submit();
                }, 200);
            });

            // Date inputs auto filter
            $('input[name="applied_from"], input[name="applied_to"]').on('change', function() {
                if (validateDateRange()) {
                    setTimeout(function() {
                        $('#candidate_filter').submit();
                    }, 300);
                }
            });
        }

        function highlightSearchTerm(element, term) {
            const walker = document.createTreeWalker(
                element[0],
                NodeFilter.SHOW_TEXT,
                null,
                false
            );

            const textNodes = [];
            let node;
            while (node = walker.nextNode()) {
                textNodes.push(node);
            }

            textNodes.forEach(textNode => {
                const text = textNode.textContent.toLowerCase();
                if (text.includes(term)) {
                    const parent = textNode.parentNode;
                    const regex = new RegExp(`(${term})`, 'gi');
                    const highlightedHTML = textNode.textContent.replace(regex, '<span class="search-highlight">$1</span>');
                    
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = highlightedHTML;
                    
                    while (tempDiv.firstChild) {
                        parent.insertBefore(tempDiv.firstChild, textNode);
                    }
                    
                    parent.removeChild(textNode);
                }
            });
        }

        function updateSummaryCards(visibleRows) {
            if (visibleRows.length === 0) return;

            let psychotest = 0;
            let interview1 = 0;
            let interview2 = 0;
            let hired = 0;
            let rejected = 0;
            let totalCandidates = visibleRows.length;

            visibleRows.each(function() {
                const stageSelect = $(this).find('.stage-select');
                const stageValue = stageSelect.length ? stageSelect.val() : null;
                
                switch(stageValue) {
                    case '2':
                        psychotest++;
                        break;
                    case '3':
                        interview1++;
                        break;
                    case '4':
                        interview2++;
                        break;
                    case '5':
                        hired++;
                        break;
                    case '6':
                        rejected++;
                        break;
                }
            });

            // Animate counter updates
            animateCounter($('.psychotest .summary-number'), psychotest);
            animateCounter($('.interview1 .summary-number'), interview1);
            animateCounter($('.interview2 .summary-number'), interview2);
            animateCounter($('.hired .summary-number'), hired);
            animateCounter($('.rejected .summary-number'), rejected);
            animateCounter($('.summary-card:not(.psychotest):not(.interview1):not(.interview2):not(.hired):not(.rejected) .summary-number'), totalCandidates);
        }

        function animateCounter($element, targetValue) {
            const currentValue = parseInt($element.text().replace(/,/g, '')) || 0;
            
            $({ countNum: currentValue }).animate({
                countNum: targetValue
            }, {
                duration: 500,
                easing: 'easeOutQuart',
                step: function() {
                    $element.text(Math.floor(this.countNum).toLocaleString());
                },
                complete: function() {
                    $element.text(targetValue.toLocaleString());
                }
            });
        }

        function validateDateRange() {
            const fromDate = $('input[name="applied_from"]').val();
            const toDate = $('input[name="applied_to"]').val();
            
            if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                show_toastr('Error', '{{__("Applied From date cannot be greater than Applied To date")}}', 'error');
                return false;
            }
            
            return true;
        }

        // Enhanced Stage Update Function for Candidates
        function updateStage(stage, id) {
            const selectElement = $(`select[onchange*="${id}"]`);
            const selectContainer = selectElement.closest('td');
            
            // Add loading state
            selectElement.prop('disabled', true).addClass('stage-updating');
            selectContainer.css('position', 'relative');
            
            $.ajax({
                url: "{{route('update-stage-job')}}",
                type: "POST",
                data: { 
                    id: id,
                    stage: stage,
                    _token: "{{ csrf_token() }}"
                },
                success: function (data) {
                    selectElement.prop('disabled', false).removeClass('stage-updating');
                    show_toastr('Success', '{{__("Candidate stage updated successfully!")}}', 'success');
                    
                    // Update summary cards after stage change
                    setTimeout(() => {
                        const visibleRows = $('.table-clean tbody tr:visible:not(.no-data-row)');
                        updateSummaryCards(visibleRows);
                    }, 100);
                    
                    // Optional: Reload page after successful update to reflect changes
                    // setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr, status, error) {
                    selectElement.prop('disabled', false).removeClass('stage-updating');
                    show_toastr('Error', '{{__("Failed to update candidate stage")}}', 'error');
                    
                    // Reset select to previous value on error
                    selectElement.val(selectElement.find('option[selected]').val());
                    
                    console.error('Error:', error);
                }
            });
        }

        // Quick Stage Update Functions
        function updateCandidateStage(stage, candidateId) {
            if (confirm("{!! __('Are you sure you want to update this candidate\'s stage?') !!}")) {
                updateStage(stage, candidateId);
            }
        }

        // Bulk Stage Update (if needed in future)
        function bulkUpdateStage(stage) {
            const selectedCandidates = $('.candidate-checkbox:checked');
            if (selectedCandidates.length === 0) {
                show_toastr('Warning', '{{__("Please select candidates to update")}}', 'warning');
                return;
            }
            
            if (confirm(`{{__('Update')}} ${selectedCandidates.length} {{__('candidates to this stage?')}}`)) {
                selectedCandidates.each(function() {
                    const candidateId = $(this).data('candidate-id');
                    updateStage(stage, candidateId);
                });
            }
        }

        function initializeCounterAnimation() {
            setTimeout(animateCounters, 200);
        }

        function animateCounters() {
            $('.summary-number').each(function() {
                const $this = $(this);
                const text = $this.text().trim();
                const countTo = parseInt(text.replace(/,/g, ''));
                if (isNaN(countTo)) return;
                
                $({ countNum: 0 }).animate({
                    countNum: countTo
                }, {
                    duration: 1500,
                    easing: 'easeOutQuart',
                    step: function() {
                        $this.text(Math.floor(this.countNum).toLocaleString());
                    },
                    complete: function() {
                        $this.text(countTo.toLocaleString());
                    }
                });
            });
        }

        function animateCards() {
            $('.clean-card, .summary-card').each(function(index) {
                $(this).css({
                    opacity: 0,
                    transform: 'translateY(30px)'
                }).delay(index * 100).animate({
                    opacity: 1
                }, 600).css({
                    transform: 'translateY(0)'
                });
            });
        }

        // Keyboard shortcuts
        $(document).keydown(function(e) {
            if (e.ctrlKey && e.keyCode === 70) {
                e.preventDefault();
                $('#liveSearchInput').focus();
            }
            
            if (e.ctrlKey && e.keyCode === 82) {
                e.preventDefault();
                window.location.href = '{{route("job.application.candidate")}}';
            }
        });

        $.easing.easeOutQuart = function(x, t, b, c, d) {
            return -c * ((t = t / d - 1) * t * t * t - 1) + b;
        };

        console.log('{{__("Job Candidate Management System with Live Search and Stage Updates initialized successfully!")}}');
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Job Candidates')}}</li>
@endsection

@section('content')
    <!-- Filter Section -->
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="clean-card">
                    <div class="card-header-clean">
                        <h6><i class="ti ti-filter me-2"></i>{{__('Advanced Filters')}}</h6>
                    </div>
                    <div class="card-body">
                        {{ Form::open(array('route' => array('job.application.candidate'),'method'=>'get','id'=>'candidate_filter')) }}
                        <div class="filter-grid">
                            <div>
                                {{ Form::label('university', __('University'), ['class' => 'form-label']) }}
                                {{ Form::select('university', $univercity, isset($_GET['university']) ? $_GET['university'] : null, ['class' => 'form-control select2 auto-filter-select', 'placeholder' => __('Select University')]) }}
                            </div>
                            <div>
                                {{ Form::label('ipk', __('IPK'), ['class' => 'form-label']) }}
                                {{ Form::select('ipk', $ipk, isset($_GET['ipk']) ? $_GET['ipk'] : null, ['class' => 'form-control select2 auto-filter-select', 'placeholder' => __('Select IPK')]) }}
                            </div>
                            <div>
                                {{ Form::label('gender', __('Gender'), ['class' => 'form-label']) }}
                                {{ Form::select('gender', ['' => __('Select Gender'), 'male' => __('Male'), 'female' => __('Female')], isset($_GET['gender']) ? $_GET['gender'] : '', ['class' => 'form-control auto-filter-select']) }}
                            </div>
                            <div>
                                {{ Form::label('status', __('Stage'), ['class' => 'form-label']) }}
                                {{ Form::select('status', ['' => __('Select Stage')] + collect($stages)->where('id', '>', 1)->pluck('title', 'id')->toArray(), isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-control auto-filter-select']) }}
                            </div>
                            <div>
                                {{ Form::label('applied_from', __('Applied From'), ['class' => 'form-label']) }}
                                {{ Form::date('applied_from', isset($_GET['applied_from']) ? $_GET['applied_from'] : '', ['class' => 'form-control', 'placeholder' => __('Start Date')]) }}
                            </div>
                            <div>
                                {{ Form::label('applied_to', __('Applied To'), ['class' => 'form-label']) }}
                                {{ Form::date('applied_to', isset($_GET['applied_to']) ? $_GET['applied_to'] : '', ['class' => 'form-control', 'placeholder' => __('End Date')]) }}
                            </div>
                            <div>
                                {{ Form::label('search', __('Search'), ['class' => 'form-label']) }}
                                {{ Form::text('search', isset($_GET['search']) ? $_GET['search'] : '', ['class' => 'form-control', 'placeholder' => __('Search by name, email, phone, city...')]) }}
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="btn-group w-100" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search me-1"></i>{{__('Apply Filters')}}
                                    </button>
                                    <a href="{{route('job.application.candidate')}}" class="btn btn-secondary">
                                        <i class="ti ti-refresh me-1"></i>{{__('Reset All')}}
                                    </a>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    @if(count($candidates) > 0)
    @php
        $psychotest = $candidates->where('stage', 2)->count();
        $interview1 = $candidates->where('stage', 3)->count();
        $interview2 = $candidates->where('stage', 4)->count();
        $hired = $candidates->where('stage', 5)->count();
        $rejected = $candidates->where('stage', 6)->count();
    @endphp
    <div class="row fade-in">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="summary-card psychotest">
                <div class="summary-number">{{ $psychotest }}</div>
                <div class="summary-label">{{__('Psychotest')}}</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="summary-card interview1">
                <div class="summary-number">{{ $interview1 }}</div>
                <div class="summary-label">{{__('Interview 1')}}</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="summary-card interview2">
                <div class="summary-number">{{ $interview2 }}</div>
                <div class="summary-label">{{__('Interview 2')}}</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="summary-card hired">
                <div class="summary-number">{{ $hired }}</div>
                <div class="summary-label">{{__('Hired')}}</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="summary-card rejected">
                <div class="summary-number">{{ $rejected }}</div>
                <div class="summary-label">{{__('Rejected')}}</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="summary-card">
                <div class="summary-number">{{ count($candidates) }}</div>
                <div class="summary-label">{{__('Total Candidates')}}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="row">
        <div class="col-md-12">
            <div class="clean-card fade-in">
                <div class="card-header-clean">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6><i class="ti ti-users me-2"></i>{{__('Job Candidate Records')}}</h6>
                        @if(count($candidates) > 0)
                            <small class="text-muted">
                                {{__('Showing')}} {{ count($candidates) }} {{__('candidates')}}
                            </small>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-clean">
                            <thead>
                                <tr>
                                    <th scope="col">{{__('Name')}}</th>
                                    <th scope="col">{{__('Applied Job')}}</th>
                                    <th scope="col">{{__('Psychotest Status')}}</th>
                                    <th scope="col">{{__('Applied At')}}</th>
                                    <th scope="col">{{__('Stage')}}</th>
                                    <th scope="col">{{__('University')}}</th>
                                    <th scope="col">{{__('IPK')}}</th>
                                    <th scope="col">{{__('Documents')}}</th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @if(count($candidates) > 0)
                                    @foreach($candidates as $candidate)
                                        <tr>
                                            <td data-label="{{__('Name')}}">
                                                <div class="candidate-info">
                                                    <div class="candidate-avatar">
                                                        @if($candidate->profile)
                                                            <img src="{{asset('/storage/uploads/job/profile/'.$candidate->profile)}}" alt="{{ $candidate->name }}" />
                                                        @else
                                                            {{ substr($candidate->name, 0, 1) }}
                                                        @endif
                                                    </div>
                                                    <div class="candidate-details">
                                                        <h6>{{ $candidate->name }}</h6>
                                                        <small>{{ $candidate->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="{{__('Applied Job')}}">
                                                <span class="info-badge">{{ !empty($candidate->jobs)?$candidate->jobs->title:'-' }}</span>
                                            </td>
                                            <td data-label="{{__('Psychotest Status')}}">
                                                @if($candidate->stage == 2)
                                                    @php
                                                        $psychotestStatus = $candidate->psychotest_status;
                                                    @endphp
                                                    @if($psychotestStatus)
                                                        <div class="info-badge {{ $psychotestStatus['class'] }}">
                                                            <i class="ti {{ $psychotestStatus['icon'] }} me-1"></i>
                                                            <small>{{ $psychotestStatus['text'] }}</small>
                                                            @if($psychotestStatus['status'] == 'scheduled')
                                                                <br><small>{{ date('d M Y, H:i', strtotime($psychotestStatus['start_time'])) }} - {{ date('H:i', strtotime($psychotestStatus['end_time'])) }}</small>
                                                            @elseif($psychotestStatus['status'] == 'in_progress')
                                                                <br><small>{{__('Started')}}: {{ date('H:i', strtotime($psychotestStatus['started_at'])) }}</small>
                                                            @elseif($psychotestStatus['status'] == 'completed')
                                                                <br><small>{{ date('d M, H:i', strtotime($psychotestStatus['started_at'])) }} - {{ date('H:i', strtotime($psychotestStatus['completed_at'])) }}</small>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="info-badge">
                                                        <small>-</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td data-label="{{__('Applied At')}}">
                                                <div class="date-badge">
                                                    <div>{{ date('d M', strtotime($candidate->created_at)) }}</div>
                                                    <small>{{ date('Y H:i', strtotime($candidate->created_at)) }}</small>
                                                </div>
                                            </td>
                                            <td data-label="{{__('Stage')}}">
                                                <select class="form-control stage-select" name="stage" onchange="updateStage(this.value, {{ $candidate->id }})">
                                                    <option value="0" hidden>{{$candidate->stage_status->title ?? __('Select Stage')}}</option>
                                                    @foreach($stages as $stage)
                                                        @if($stage->id > 1) {{-- Only show stages beyond "Applied" --}}
                                                            <option value="{{ $stage->id }}" @if($candidate->stage == $stage->id) selected @endif>{{ $stage->title }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td data-label="{{__('University')}}">
                                                <span class="info-badge">{{ !empty($candidate->university) ? $candidate->university : '-' }}</span>
                                            </td>
                                            <td data-label="{{__('IPK')}}">
                                                <span class="info-badge">{{ !empty($candidate->ipk) ? $candidate->ipk : '-' }}</span>
                                            </td>
                                            <td data-label="{{__('Documents')}}">
                                                <div class="action-buttons">
                                                    @if(!empty($candidate->resume))
                                                        <a href="{{asset(Storage::url('uploads/job/resume')).'/'.$candidate->resume}}" target="_blank" 
                                                           class="action-btn bg-primary" data-bs-toggle="tooltip" title="{{__('Resume')}}">
                                                            <i class="ti ti-file-text"></i>
                                                        </a>
                                                    @endif
                                                    @if(!empty($candidate->kk))
                                                        <a href="{{asset(Storage::url('uploads/job/kk')).'/'.$candidate->kk}}" target="_blank" 
                                                           class="action-btn bg-success" data-bs-toggle="tooltip" title="{{__('KK')}}">
                                                            <i class="ti ti-id"></i>
                                                        </a>
                                                    @endif
                                                    @if(!empty($candidate->ktp))
                                                        <a href="{{asset(Storage::url('uploads/job/ktp')).'/'.$candidate->ktp}}" target="_blank" 
                                                           class="action-btn bg-warning" data-bs-toggle="tooltip" title="{{__('KTP')}}">
                                                            <i class="ti ti-credit-card"></i>
                                                        </a>
                                                    @endif
                                                    @if(!empty($candidate->ijazah))
                                                        <a href="{{asset(Storage::url('uploads/job/ijazah')).'/'.$candidate->ijazah}}" target="_blank" 
                                                           class="action-btn bg-primary" data-bs-toggle="tooltip" title="{{__('Ijazah')}}">
                                                            <i class="ti ti-certificate"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td data-label="{{__('Action')}}">
                                                <div class="action-buttons">
                                                    @can('show job application')
                                                        <div class="action-btn bg-primary">
                                                            <a href="{{ route('job-application.show',\Crypt::encrypt($candidate->id)) }}" 
                                                               class="mx-3 btn btn-sm align-items-center text-white" 
                                                               data-bs-toggle="tooltip" data-bs-original-title="{{__('View ').$candidate->name}}">
                                                                <i class="ti ti-eye"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @if($candidate->stage != 6)
                                                        <div class="action-btn bg-danger">
                                                            <a href="#" onclick="updateCandidateStage(6, {{ $candidate->id }})" 
                                                               class="mx-3 btn btn-sm align-items-center text-white" 
                                                               data-bs-toggle="tooltip" data-bs-original-title="{{__('Reject Candidate')}}">
                                                                <i class="ti ti-x"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="no-data-row">
                                        <td colspan="9">
                                            <div class="empty-state">
                                                <div class="empty-state-icon">
                                                    <i class="ti ti-users-off"></i>
                                                </div>
                                                <h5>{{__('No candidates found')}}</h5>
                                                <p>{{__('No job candidates found for the selected criteria. Candidates appear here after passing the initial application stage.')}}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Pagination -->
                @if(count($candidates) > 0)
                    <div class="d-flex justify-content-center mt-3 p-3">
                        {{ $candidates->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection