@extends('layouts.admin')
@section('page-title')
    {{__('Manage Job Application')}}
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

        .summary-card.total-applications {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .summary-card.pending-review {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .summary-card.interviewed {
            background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
        }

        .summary-card.hired {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
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

        /* Applicant info styling */
        .applicant-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .applicant-avatar {
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

        .applicant-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .applicant-details h6 {
            margin: 0;
            font-weight: 600;
            color: #495057;
        }

        .applicant-details small {
            color: #6c757d;
        }

        /* Status dropdown styling */
        .status-select {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            border: 1px solid #90caf9;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
            min-width: 120px;
        }

        .status-select:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.25);
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

        .action-btn.bg-danger {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            color: white;
        }

        /* Info badge styling */
        .info-badge {
            background: #f8f9fa;
            color: #495057;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
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

        /* Auto-filter styling */
        .auto-filter-select {
            transition: all 0.3s ease;
        }

        .auto-filter-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
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

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
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

            .table-clean thead th,
            .table-clean tbody td {
                padding: 0.5rem 0.25rem;
            }

            .applicant-info {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .applicant-avatar {
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

        /* Search clear button */
        .live-search-container {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-clear-btn {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .search-clear-btn:hover {
            background-color: #e9ecef;
            color: #495057;
        }

        /* Search highlight */
        .search-highlight {
            background-color: #ffeb3b;
            padding: 0 2px;
            border-radius: 2px;
            font-weight: 600;
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

                $('.select2').on('select2:open', function() {
                    $('.select2-search__field').attr('placeholder', '{{__("Type to search...")}}');
                });
            }
        }

        function initializeFormHandlers() {
            $('input[name="applied_from"], input[name="applied_to"]').on('change', validateDateRange);
        }

        // DataTable-style Live Search Functionality
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
                    resultsCount.show().html(`<i class="ti ti-check-circle"></i> {{__('Found')}} <strong>${visibleCount}</strong> {{__('results for')}} "<strong>${searchTerm}</strong>"`);
                    paginationContainer.hide(); // Hide pagination when searching
                } else {
                    resultsCount.show().html(`<i class="ti ti-alert-circle"></i> {{__('No results found for')}} "<strong>${searchTerm}</strong>"`);
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
                                        <h5>{{__('No results found')}}</h5>
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
                resultsCount.show().html('<i class="ti ti-loader rotating"></i> {{__("Searching...")}}');
                
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
                applied_from: $('input[name="applied_from"]').val(),
                applied_to: $('input[name="applied_to"]').val(),
                search: searchTerm
            };

            $.ajax({
                url: "{{ route('job-application.index') }}",
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
                        resultsCount.html(`<i class="ti ti-check-circle"></i> {{__('Found')}} ${resultsText} {{__('results for')}} "<strong>${searchTerm}</strong>"`);
                        
                        // Highlight search terms in results
                        highlightSearchTerms(searchTerm);
                        
                        // Update summary cards with new data
                        updateSummaryCardsFromResults();
                    } else {
                        resultsCount.html(`<i class="ti ti-alert-circle"></i> {{__('No results found for')}} "<strong>${searchTerm}</strong>"`);
                        
                        // Show empty state
                        tableBody.html(`
                            <tr class="no-data-row">
                                <td colspan="9">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="ti ti-search-off"></i>
                                        </div>
                                        <h5>{{__('No results found')}}</h5>
                                        <p>{{__('No applications found for')}} "<strong>${searchTerm}</strong>"</p>
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

            let totalApplications = visibleRows.length;
            let pendingReview = 0;
            let interviewed = 0;
            let hired = 0;

            visibleRows.each(function() {
                const stageText = $(this).find('.status-select option:selected').text().toLowerCase();
                
                if (stageText.includes('applied') || stageText.includes('psychotest')) {
                    pendingReview++;
                } else if (stageText.includes('interview')) {
                    interviewed++;
                } else if (stageText.includes('hired')) {
                    hired++;
                }
            });

            // Animate counter updates
            animateCounter($('.total-applications .summary-number'), totalApplications);
            animateCounter($('.pending-review .summary-number'), pendingReview);
            animateCounter($('.interviewed .summary-number'), interviewed);
            animateCounter($('.hired .summary-number'), hired);
        }

        function clearSearch() {
            $('#liveSearchInput').val('');
            const tableBody = $('.table-clean tbody');
            const originalTableContent = $('.table-clean tbody').data('original-content');
            
            // Restore original content
            location.reload(); // Simple way to restore original state
        }');
        }

        function updateSummaryCardsFromVisible(visibleRows = null) {
            const rows = visibleRows || $('.table-clean tbody tr:visible:not(.no-data-row):not(.no-search-results)');
            
            if (rows.length === 0) {
                // Reset to original values when no visible rows
                const originalTotal = $('.table-clean tbody tr:not(.no-data-row):not(.no-search-results)').length;
                animateCounter($('.total-applications .summary-number'), originalTotal);
                return;
            }

            let totalApplications = rows.length;
            let pendingReview = 0;
            let interviewed = 0;
            let hired = 0;

            rows.each(function() {
                const stageSelect = $(this).find('.status-select');
                if (stageSelect.length) {
                    const selectedOption = stageSelect.find('option:selected');
                    const stageText = selectedOption.text().toLowerCase();
                    
                    if (stageText.includes('applied') || stageText.includes('psychotest')) {
                        pendingReview++;
                    } else if (stageText.includes('interview')) {
                        interviewed++;
                    } else if (stageText.includes('hired')) {
                        hired++;
                    }
                }
            });

            // Animate counter updates
            animateCounter($('.total-applications .summary-number'), totalApplications);
            animateCounter($('.pending-review .summary-number'), pendingReview);
            animateCounter($('.interviewed .summary-number'), interviewed);
            animateCounter($('.hired .summary-number'), hired);
        }

        // Auto Filters (without submit button)
        function initializeAutoFilters() {
            const filterSelects = $('.auto-filter-select');
            
            filterSelects.on('change', function() {
                // Add slight delay to make it feel more natural
                setTimeout(function() {
                    $('#job_application').submit();
                }, 200);
            });

            // Date inputs auto filter
            $('input[name="applied_from"], input[name="applied_to"]').on('change', function() {
                if (validateDateRange()) {
                    setTimeout(function() {
                        $('#job_application').submit();
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

            let totalApplications = visibleRows.length;
            let pendingReview = 0;
            let interviewed = 0;
            let hired = 0;

            visibleRows.each(function() {
                const stageText = $(this).find('.status-select option:selected').text().toLowerCase();
                
                if (stageText.includes('applied') || stageText.includes('psychotest')) {
                    pendingReview++;
                } else if (stageText.includes('interview')) {
                    interviewed++;
                } else if (stageText.includes('hired')) {
                    hired++;
                }
            });

            // Animate counter updates
            animateCounter($('.total-applications .summary-number'), totalApplications);
            animateCounter($('.pending-review .summary-number'), pendingReview);
            animateCounter($('.interviewed .summary-number'), interviewed);
            animateCounter($('.hired .summary-number'), hired);
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

        function updateStage(stage, id) {
            const selectElement = $(`select[onchange*="${id}"]`);
            selectElement.prop('disabled', true);
            
            $.ajax({
                url: "{{route('update-stage-job')}}",
                type: "POST",
                data: { 
                    id: id,
                    stage: stage,
                    _token: "{{ csrf_token() }}"
                },
                success: function (data) {
                    selectElement.prop('disabled', false);
                    show_toastr('Success', '{{__("Application status updated successfully!")}}', 'success');
                    
                    // Update summary cards after stage change
                    setTimeout(() => {
                        const visibleRows = $('.table-clean tbody tr:visible');
                        updateSummaryCards(visibleRows);
                    }, 100);
                },
                error: function(xhr, status, error) {
                    selectElement.prop('disabled', false);
                    show_toastr('Error', '{{__("Failed to update status")}}', 'error');
                    console.error('Error:', error);
                }
            });
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

        $(document).keydown(function(e) {
            if (e.ctrlKey && e.keyCode === 70) {
                e.preventDefault();
                $('#liveSearchInput').focus();
            }
            
            if (e.ctrlKey && e.keyCode === 82) {
                e.preventDefault();
                window.location.href = '{{route("job-application.index")}}';
            }
        });

        $.easing.easeOutQuart = function(x, t, b, c, d) {
            return -c * ((t = t / d - 1) * t * t * t - 1) + b;
        };

        console.log('{{__("Job Application Management System with Live Search initialized successfully!")}}');
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Job Application')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        {{-- Filter toggle can be added here if needed --}}
    </div>
@endsection

@section('content')

    <!-- Filter Section -->
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="clean-card">
                    <div class="card-header-clean">
                        <h6><i class="ti ti-filter me-2"></i>{{__('Advanced Filters')}}</h6>
                    </div>
                    <div class="card-body">
                        {{ Form::open(array('route' => array('job-application.index'),'method'=>'get','id'=>'job_application')) }}
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
                                    <a href="{{route('job-application.index')}}" class="btn btn-secondary">
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
    @if(count($applicants) > 0)
    @php
        $totalApplications = count($applicants);
        $pendingReview = $applicants->whereIn('stage', [1, 2])->count();
        $interviewed = $applicants->where('stage', 3)->count();
        $hired = $applicants->where('stage', 4)->count();
    @endphp
    <div class="row fade-in">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card total-applications">
                <div class="summary-number">{{ $totalApplications }}</div>
                <div class="summary-label">{{__('Total Applications')}}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card pending-review">
                <div class="summary-number">{{ $pendingReview }}</div>
                <div class="summary-label">{{__('Pending Review')}}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card interviewed">
                <div class="summary-number">{{ $interviewed }}</div>
                <div class="summary-label">{{__('Interviewed')}}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card hired">
                <div class="summary-number">{{ $hired }}</div>
                <div class="summary-label">{{__('Hired')}}</div>
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
                        <h6><i class="ti ti-users me-2"></i>{{__('Job Application Records')}}</h6>
                        @if(count($applicants) > 0)
                            <small class="text-muted">
                                {{__('Showing')}} {{ count($applicants) }} {{__('records')}}
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
                                    <th scope="col">{{__('Applied At')}}</th>
                                    <th scope="col">{{__('DoB')}}</th>
                                    <th scope="col">{{__('Gender')}}</th>
                                    <th scope="col">{{__('Phone')}}</th>
                                    <th scope="col">{{__('Email')}}</th>
                                    <th scope="col">{{__('City')}}</th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @if(count($applicants) > 0)
                                    @foreach($applicants as $applicant)
                                        <tr>
                                            <td data-label="{{__('Name')}}">
                                                <div class="applicant-info">
                                                    <div class="applicant-avatar">
                                                        @if($applicant->profile)
                                                            <img src="{{asset('/storage/uploads/job/profile/'.$applicant->profile)}}" alt="{{ $applicant->name }}" />
                                                        @else
                                                            {{ substr($applicant->name, 0, 1) }}
                                                        @endif
                                                    </div>
                                                    <div class="applicant-details">
                                                        <h6>{{ $applicant->name }}</h6>
                                                        <small>{{ $applicant->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="{{__('Applied At')}}">
                                                <div class="date-badge">
                                                    <div>{{ date('d M', strtotime($applicant->created_at)) }}</div>
                                                    <small>{{ date('Y H:i', strtotime($applicant->created_at)) }}</small>
                                                </div>
                                            </td>
                                            <td data-label="{{__('DoB')}}">
                                                @if(!empty($applicant->dob))
                                                    <div class="date-badge">
                                                        <div>{{ date('d M', strtotime($applicant->dob)) }}</div>
                                                        <small>{{ date('Y', strtotime($applicant->dob)) }}</small>
                                                    </div>
                                                @else
                                                    <span class="info-badge">-</span>
                                                @endif
                                            </td>
                                            <td data-label="{{__('Gender')}}">
                                                @if(!empty($applicant->gender))
                                                    <span class="gender-badge">{{ucfirst($applicant->gender)}}</span>
                                                @else
                                                    <span class="info-badge">-</span>
                                                @endif
                                            </td>
                                            <td data-label="{{__('Phone')}}">
                                                <span class="info-badge">{{!empty($applicant->phone)?$applicant->phone:'-'}}</span>
                                            </td>
                                            <td data-label="{{__('Email')}}">
                                                <span class="info-badge">{{!empty($applicant->email)?$applicant->email:'-'}}</span>
                                            </td>
                                            <td data-label="{{__('City')}}">
                                                <span class="info-badge">{{!empty($applicant->city)?$applicant->city:'-'}}</span>
                                            </td>
                                            <td data-label="{{__('Status')}}">
                                                <select class="form-control status-select" name="stage" onchange="updateStage(this.value, {{ $applicant->id }})">
                                                    <option value="0" hidden>{{$applicant->stage_status->title ?? __('Select Status')}}</option>
                                                    @foreach($stages as $stage)
                                                        <option value="{{ $stage->id }}" @if($applicant->stage == $stage->id) selected @endif>{{ $stage->title }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td data-label="{{__('Action')}}">
                                                <div class="action-buttons">
                                                    @can('show job application')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="{{ route('job-application.show',\Crypt::encrypt($applicant->id)) }}" 
                                                            class="mx-3 btn btn-sm align-items-center text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('View ').$applicant->name}}">
                                                                <i class="ti ti-eye"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete job application')
                                                        <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['job-application.destroy', $applicant->id],'id'=>'delete-form-'.$applicant->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para text-white" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm-yes="document.getElementById('delete-form-{{$applicant->id}}').submit();">
                                                            <i class="ti ti-trash"></i></a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
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
                                                <h5>{{__('No applicants found')}}</h5>
                                                <p>{{__('No job applications found for the selected criteria.')}}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                                    <!-- Pagination -->
                @if(count($applicants) > 0)
                    <div class="d-flex justify-content-center mt-3 p-3">
                        {{ $applicants->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection