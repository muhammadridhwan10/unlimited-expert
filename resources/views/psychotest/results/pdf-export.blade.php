<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Psychotest Report - {{ $schedule->candidates->name }}</title>
    <style>
        @page {
            margin: 15mm;
            @top-center {
                content: "PSYCHOTEST ASSESSMENT REPORT";
                font-size: 12px;
                color: #666;
            }
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 10px;
                color: #666;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #2c5aa0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #2c5aa0;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header h2 {
            color: #666;
            margin: 5px 0;
            font-size: 16px;
            font-weight: normal;
        }
        
        .candidate-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .candidate-info h3 {
            margin: 0 0 10px 0;
            color: #2c5aa0;
            font-size: 14px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            padding: 3px 10px 3px 0;
        }
        
        .info-value {
            display: table-cell;
            padding: 3px 0;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background: #2c5aa0;
            color: white;
            padding: 8px 15px;
            margin: 0 0 15px 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .overall-score {
            text-align: center;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .score-circle {
            display: inline-block;
            width: 100px;
            height: 100px;
            border: 8px solid #28a745;
            border-radius: 50%;
            position: relative;
            margin-bottom: 10px;
        }
        
        .score-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            font-weight: bold;
            color: #2c5aa0;
        }
        
        .grade-badge {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: bold;
            margin: 0 10px;
        }
        
        .decision-box {
            border: 2px solid #2c5aa0;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        
        .decision-status {
            font-size: 16px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 5px;
        }
        
        .confidence-level {
            color: #666;
            font-size: 12px;
        }
        
        .risk-level {
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            display: inline-block;
            margin: 5px 0;
        }
        
        .risk-low { background: #d4edda; color: #155724; }
        .risk-medium { background: #fff3cd; color: #856404; }
        .risk-high { background: #f8d7da; color: #721c24; }
        
        .category-result {
            border: 1px solid #dee2e6;
            margin-bottom: 15px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .category-header {
            background: #e9ecef;
            padding: 10px 15px;
            font-weight: bold;
            color: #495057;
        }
        
        .category-content {
            padding: 15px;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            margin: 10px 0;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 10px;
            position: absolute;
            top: 0;
            left: 0;
        }
        
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 11px;
            font-weight: bold;
            color: white;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
        }
        
        .performance-excellent { background: #28a745; }
        .performance-good { background: #17a2b8; }
        .performance-fair { background: #ffc107; }
        .performance-poor { background: #dc3545; }
        
        .insights-list {
            margin: 10px 0;
        }
        
        .insight-item {
            margin: 5px 0;
            padding: 5px 0 5px 15px;
            border-left: 3px solid #28a745;
            background: #f8f9fa;
        }
        
        .recommendations {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #2c5aa0;
        }
        
        .recommendations h4 {
            margin: 0 0 10px 0;
            color: #2c5aa0;
        }
        
        .recommendation-item {
            margin: 8px 0;
            padding-left: 15px;
            position: relative;
        }
        
        .recommendation-item:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
        }
        
        .strengths-weaknesses {
            display: table;
            width: 100%;
            margin: 15px 0;
        }
        
        .strengths, .weaknesses {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .strengths {
            padding-right: 10px;
        }
        
        .weaknesses {
            padding-left: 10px;
        }
        
        .strength-item {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 3px;
            color: #155724;
        }
        
        .weakness-item {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 3px;
            color: #721c24;
        }
        
        .personality-analysis {
            background: #f0f8ff;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .personality-traits {
            display: table;
            width: 100%;
        }
        
        .trait-row {
            display: table-row;
        }
        
        .trait-name {
            display: table-cell;
            padding: 5px 10px 5px 0;
            font-weight: bold;
            width: 40%;
        }
        
        .trait-score {
            display: table-cell;
            padding: 5px 0;
        }
        
        .trait-bar {
            width: 100px;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            position: relative;
            display: inline-block;
            margin-right: 10px;
        }
        
        .trait-fill {
            height: 100%;
            background: #17a2b8;
            border-radius: 4px;
            position: absolute;
            top: 0;
            left: 0;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .text-muted { color: #666; }
        
        /* Prevent orphans */
        h1, h2, h3, h4 {
            page-break-after: avoid;
        }
        
        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>PSYCHOTEST ASSESSMENT REPORT</h1>
        <h2>Comprehensive Psychological Evaluation</h2>
        <p class="text-muted">Generated on {{ $exportDate->format('d F Y, H:i') }}</p>
    </div>

    <!-- Candidate Information -->
    <div class="candidate-info">
        <h3>CANDIDATE INFORMATION</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Full Name:</div>
                <div class="info-value">{{ $schedule->candidates->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email Address:</div>
                <div class="info-value">{{ $schedule->candidates->email }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Position Applied:</div>
                <div class="info-value">{{ $schedule->candidates->jobs->title ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Test Date:</div>
                <div class="info-value">{{ $schedule->start_time->format('d F Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Test Duration:</div>
                <div class="info-value">{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Time Spent:</div>
                <div class="info-value">{{ $performanceMetrics['total_time'] ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Overall Performance Section -->
    <div class="section">
        <h3 class="section-title">OVERALL PERFORMANCE SUMMARY</h3>
        
        <div class="overall-score">
            <div class="score-circle">
                <div class="score-text">{{ $performanceMetrics['overall_score'] ?? 0 }}%</div>
            </div>
            <br>
            <span class="grade-badge">GRADE {{ $performanceMetrics['grade'] ?? 'N/A' }}</span>
        </div>

        <!-- Decision Status -->
        @if(isset($performanceMetrics['decision_status']))
        <div class="decision-box">
            <div class="decision-status">HIRING DECISION: {{ $performanceMetrics['decision_status'] }}</div>
            @if(isset($performanceMetrics['decision_confidence']))
            <div class="confidence-level">Confidence Level: {{ $performanceMetrics['decision_confidence'] }}%</div>
            @endif
            
            @if(isset($performanceMetrics['risk_level']))
            <div style="margin-top: 10px;">
                <span class="risk-level risk-{{ strtolower($performanceMetrics['risk_level']) }}">
                    RISK LEVEL: {{ $performanceMetrics['risk_level'] }}
                </span>
            </div>
            @endif
        </div>
        @endif

        <!-- Strengths and Weaknesses -->
        @if((isset($performanceMetrics['strengths']) && count($performanceMetrics['strengths']) > 0) || 
            (isset($performanceMetrics['weaknesses']) && count($performanceMetrics['weaknesses']) > 0))
        <div class="strengths-weaknesses">
            @if(isset($performanceMetrics['strengths']) && count($performanceMetrics['strengths']) > 0)
            <div class="strengths">
                <h4 style="color: #155724; margin-bottom: 10px;">Key Strengths:</h4>
                @foreach($performanceMetrics['strengths'] as $strength)
                <div class="strength-item">
                    {{ $strength['category'] }} ({{ $strength['score'] }}%)
                </div>
                @endforeach
            </div>
            @endif

            @if(isset($performanceMetrics['weaknesses']) && count($performanceMetrics['weaknesses']) > 0)
            <div class="weaknesses">
                <h4 style="color: #721c24; margin-bottom: 10px;">Areas for Improvement:</h4>
                @foreach($performanceMetrics['weaknesses'] as $weakness)
                <div class="weakness-item">
                    {{ $weakness['category'] }} ({{ $weakness['score'] }}%)
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Category Breakdown -->
    <div class="section">
        <h3 class="section-title">DETAILED CATEGORY ANALYSIS</h3>
        
        @if(isset($categoryAnalysis) && count($categoryAnalysis) > 0)
            @foreach($categoryAnalysis as $category)
            <div class="category-result">
                <div class="category-header">
                    {{ $category['category_name'] }}
                    <span style="float: right; font-weight: normal;">
                        {{ $category['performance_level'] ?? 'N/A' }}
                    </span>
                </div>
                <div class="category-content">
                    <!-- Category Description -->
                    @if(!empty($category['description']))
                    <p class="text-muted">{{ $category['description'] }}</p>
                    @endif

                    <!-- Performance Bar -->
                    <div class="progress-bar">
                        <div class="progress-fill performance-{{ strtolower(str_replace(' ', '-', $category['performance_level'] ?? 'fair')) }}" 
                             style="width: {{ $category['percentage'] }}%;">
                        </div>
                        <div class="progress-text">{{ $category['percentage'] }}%</div>
                    </div>

                    <!-- Statistics -->
                    <div style="margin: 15px 0;">
                        <table style="font-size: 10px;">
                            <tr>
                                <td><strong>Questions Answered:</strong></td>
                                <td>{{ $category['answered_questions'] }}/{{ $category['total_questions'] }}</td>
                                <td><strong>Points Earned:</strong></td>
                                <td>{{ $category['points']['earned'] }}/{{ $category['points']['total'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Time Spent:</strong></td>
                                <td>{{ $category['time_spent'] }}</td>
                                <td><strong>Status:</strong></td>
                                <td>{{ ucfirst($category['status']) }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Category Insights -->
                    @if(isset($category['insights']) && count($category['insights']) > 0)
                    <div class="insights-list">
                        <strong>Key Insights:</strong>
                        @foreach($category['insights'] as $insight)
                        <div class="insight-item">{{ $insight }}</div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Field Breakdown (for field test) -->
                    @if(isset($category['field_breakdown']) && count($category['field_breakdown']) > 0)
                    <div style="margin-top: 15px;">
                        <strong>Field Performance Breakdown:</strong>
                        @foreach($category['field_breakdown'] as $field => $percentage)
                        <div style="margin: 5px 0;">
                            <span>{{ ucfirst($field) }}:</span>
                            <div class="progress-bar" style="width: 200px; height: 12px; display: inline-block; margin: 0 10px;">
                                <div class="progress-fill performance-{{ $percentage >= 70 ? 'excellent' : ($percentage >= 50 ? 'good' : 'poor') }}" 
                                     style="width: {{ $percentage }}%;"></div>
                                <div class="progress-text" style="font-size: 9px;">{{ $percentage }}%</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- EPPS Personality Dimensions -->
                    @if(isset($category['dimension_scores']) && count($category['dimension_scores']) > 0)
                    <div style="margin-top: 15px;">
                        <strong>Personality Dimension Scores:</strong>
                        <div class="personality-traits">
                            @foreach(array_slice($category['dimension_scores'], 0, 10) as $dimension => $score)
                            <div class="trait-row">
                                <div class="trait-name">{{ ucfirst($dimension) }}:</div>
                                <div class="trait-score">
                                    <div class="trait-bar">
                                        <div class="trait-fill" style="width: {{ $score }}%;"></div>
                                    </div>
                                    {{ round($score) }}%
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Work Performance Predictions -->
    @if(isset($workPredictions) && count($workPredictions) > 0)
    <div class="section">
        <h3 class="section-title">WORK PERFORMANCE PREDICTIONS</h3>
        <div class="recommendations">
            <h4>Predicted Work Behavior & Performance:</h4>
            @foreach($workPredictions as $prediction)
            <div class="recommendation-item">{{ $prediction }}</div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Division Recommendation -->
    @if(isset($divisionRecommendation))
    <div class="section">
        <h3 class="section-title">DIVISION & PLACEMENT RECOMMENDATION</h3>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
            <h4 style="margin: 0 0 10px 0; color: #2c5aa0;">Recommended Division:</h4>
            <p style="font-size: 14px; font-weight: bold; color: #155724;">
                {{ $divisionRecommendation['primary_division'] ?? 'General Division' }}
            </p>
            <p><strong>Reasoning:</strong> {{ $divisionRecommendation['reasoning'] ?? 'Based on overall assessment' }}</p>
            <p><strong>Confidence Level:</strong> {{ $divisionRecommendation['confidence'] ?? 'Medium' }}%</p>
            
            @if(isset($divisionRecommendation['alternative']))
            <p><strong>Alternative Division:</strong> {{ $divisionRecommendation['alternative'] }}</p>
            @endif
        </div>
    </div>
    @endif

    <!-- Personality Analysis (EPPS) -->
    @if(isset($personalityAnalysis) && $personalityAnalysis)
    <div class="section">
        <h3 class="section-title">PERSONALITY ANALYSIS (EPPS)</h3>
        <div class="personality-analysis">
            <h4>Personality Profile Summary:</h4>
            <div style="margin: 15px 0;">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Personality Type:</div>
                        <div class="info-value">{{ $personalityAnalysis['personality_type'] ?? 'Balanced Professional' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Work Style:</div>
                        <div class="info-value">{{ $personalityAnalysis['work_style'] ?? 'Adaptable' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Primary Motivation:</div>
                        <div class="info-value">{{ $personalityAnalysis['primary_motivation'] ?? 'Achievement-oriented' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Communication Style:</div>
                        <div class="info-value">{{ $personalityAnalysis['communication_style'] ?? 'Balanced communicator' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Leadership Style:</div>
                        <div class="info-value">{{ $personalityAnalysis['leadership_style'] ?? 'Situational leadership' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Team Dynamics:</div>
                        <div class="info-value">{{ $personalityAnalysis['team_fit'] ?? 'Flexible team member' }}</div>
                    </div>
                </div>
            </div>

            @if(isset($personalityAnalysis['workplace_strengths']) && count($personalityAnalysis['workplace_strengths']) > 0)
            <h4>Workplace Personality Strengths:</h4>
            <div class="insights-list">
                @foreach($personalityAnalysis['workplace_strengths'] as $strength)
                <div class="insight-item">{{ $strength }}</div>
                @endforeach
            </div>
            @endif

            @if(isset($personalityAnalysis['potential_challenges']) && count($personalityAnalysis['potential_challenges']) > 0)
            <h4>Potential Personality Challenges:</h4>
            <div style="margin: 10px 0;">
                @foreach($personalityAnalysis['potential_challenges'] as $challenge)
                <div style="margin: 5px 0; padding: 5px 0 5px 15px; border-left: 3px solid #ffc107; background: #fff3cd;">
                    {{ $challenge }}
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Risk Assessment -->
    @if(isset($riskAssessment) && $riskAssessment)
    <div class="section">
        <h3 class="section-title">RISK ASSESSMENT & MITIGATION</h3>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
            <div style="margin-bottom: 15px;">
                <strong>Risk Level: </strong>
                <span class="risk-level risk-{{ strtolower($riskAssessment['level']) }}">
                    {{ $riskAssessment['level'] }}
                </span>
                <span style="margin-left: 10px; color: #666;">
                    (Risk Score: {{ $riskAssessment['score'] }}/100)
                </span>
            </div>

            @if(isset($riskAssessment['factors']) && count($riskAssessment['factors']) > 0)
            <h4>Risk Factors Identified:</h4>
            <div style="margin: 10px 0;">
                @foreach($riskAssessment['factors'] as $factor)
                <div style="margin: 5px 0; padding: 8px 12px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 3px; color: #721c24;">
                    {{ $factor }}
                </div>
                @endforeach
            </div>
            @endif

            @if(isset($riskAssessment['mitigation']) && count($riskAssessment['mitigation']) > 0)
            <h4>Recommended Risk Mitigation Strategies:</h4>
            <div class="recommendations">
                @foreach($riskAssessment['mitigation'] as $mitigation)
                <div class="recommendation-item">{{ $mitigation }}</div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Main Recommendations -->
    <div class="section">
        <h3 class="section-title">COMPREHENSIVE RECOMMENDATIONS</h3>
        <div class="recommendations">
            <h4>Summary & Action Items:</h4>
            <div style="white-space: pre-line; line-height: 1.6;">
                {{ $performanceMetrics['recommendation'] ?? 'No specific recommendations available at this time.' }}
            </div>
        </div>
    </div>

    <!-- Insights & Additional Notes -->
    @if(isset($performanceMetrics['insights']) && count($performanceMetrics['insights']) > 0)
    <div class="section">
        <h3 class="section-title">KEY INSIGHTS & OBSERVATIONS</h3>
        <div class="insights-list">
            @foreach($performanceMetrics['insights'] as $insight)
            <div class="insight-item">{{ $insight }}</div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Development Plan -->
    @if(isset($performanceMetrics['development_plan']))
    <div class="section">
        <h3 class="section-title">DEVELOPMENT PLAN</h3>
        <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; border-left: 4px solid #2c5aa0;">
            @php $devPlan = $performanceMetrics['development_plan']; @endphp
            
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Priority Level:</div>
                    <div class="info-value"><strong>{{ $devPlan['priority'] ?? 'MEDIUM' }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Estimated Duration:</div>
                    <div class="info-value">{{ $devPlan['duration'] ?? '3-6 months' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Primary Focus:</div>
                    <div class="info-value">{{ $devPlan['primary_focus'] ?? 'General skill enhancement' }}</div>
                </div>
            </div>

            @if(isset($devPlan['focus_areas']) && count($devPlan['focus_areas']) > 0)
            <h4>Focus Areas:</h4>
            <ul style="margin: 5px 0; padding-left: 20px;">
                @foreach($devPlan['focus_areas'] as $area)
                <li style="margin: 3px 0;">{{ $area }}</li>
                @endforeach
            </ul>
            @endif

            @if(isset($devPlan['training_modules']) && count($devPlan['training_modules']) > 0)
            <h4>Required Training Modules:</h4>
            <ul style="margin: 5px 0; padding-left: 20px;">
                @foreach($devPlan['training_modules'] as $module)
                <li style="margin: 3px 0;">{{ $module }}</li>
                @endforeach
            </ul>
            @endif

            @if(isset($devPlan['success_metrics']) && count($devPlan['success_metrics']) > 0)
            <h4>Success Metrics:</h4>
            <ul style="margin: 5px 0; padding-left: 20px;">
                @foreach($devPlan['success_metrics'] as $metric)
                <li style="margin: 3px 0;">{{ $metric }}</li>
                @endforeach
            </ul>
            @endif

            @if(isset($devPlan['resources_needed']) && count($devPlan['resources_needed']) > 0)
            <h4>Resources Needed:</h4>
            <ul style="margin: 5px 0; padding-left: 20px;">
                @foreach($devPlan['resources_needed'] as $resource)
                <li style="margin: 3px 0;">{{ $resource }}</li>
                @endforeach
            </ul>
            @endif

            @if(isset($devPlan['timeline']) && count($devPlan['timeline']) > 0)
            <h4>Development Timeline:</h4>
            <table style="font-size: 10px; margin: 10px 0;">
                @foreach($devPlan['timeline'] as $period => $activities)
                <tr>
                    <td style="font-weight: bold; width: 20%;">{{ $period }}:</td>
                    <td>{{ $activities }}</td>
                </tr>
                @endforeach
            </table>
            @endif
        </div>
    </div>
    @endif

    <!-- Next Steps -->
    @if(isset($performanceMetrics['next_steps']) && count($performanceMetrics['next_steps']) > 0)
    <div class="section">
        <h3 class="section-title">RECOMMENDED NEXT STEPS</h3>
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
            <h4>Immediate Action Items:</h4>
            <ol style="margin: 10px 0; padding-left: 20px;">
                @foreach(array_slice($performanceMetrics['next_steps'], 0, 8) as $step)
                <li style="margin: 5px 0; line-height: 1.4;">{{ $step }}</li>
                @endforeach
            </ol>
            
            @if(count($performanceMetrics['next_steps']) > 8)
            <p style="font-style: italic; color: #666; margin-top: 10px;">
                ... and {{ count($performanceMetrics['next_steps']) - 8 }} additional steps detailed in the comprehensive recommendation above.
            </p>
            @endif
        </div>
    </div>
    @endif

    <!-- Report Summary -->
    <div class="section">
        <h3 class="section-title">REPORT SUMMARY</h3>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; border: 2px solid #2c5aa0;">
            <h4 style="color: #2c5aa0; margin-bottom: 15px;">Executive Summary:</h4>
            
            <div style="margin-bottom: 15px;">
                <strong>Candidate:</strong> {{ $schedule->candidates->name }}<br>
                <strong>Position:</strong> {{ $schedule->candidates->jobs->title ?? 'N/A' }}<br>
                <strong>Overall Score:</strong> {{ $performanceMetrics['overall_score'] ?? 0 }}% (Grade {{ $performanceMetrics['grade'] ?? 'N/A' }})<br>
                <strong>Decision:</strong> {{ $performanceMetrics['decision_status'] ?? 'Under Review' }}<br>
                <strong>Risk Level:</strong> {{ $performanceMetrics['risk_level'] ?? 'MEDIUM' }}
            </div>

            <div style="border-top: 1px solid #dee2e6; padding-top: 10px; margin-top: 15px;">
                <strong>Key Takeaway:</strong>
                <p style="margin: 5px 0; font-style: italic;">
                    @if(($performanceMetrics['overall_score'] ?? 0) >= 80)
                        This candidate demonstrates strong potential and is highly recommended for the position with minimal risk factors.
                    @elseif(($performanceMetrics['overall_score'] ?? 0) >= 65)
                        This candidate shows good potential with some areas for development. Recommended with targeted training support.
                    @elseif(($performanceMetrics['overall_score'] ?? 0) >= 50)
                        This candidate requires careful consideration and substantial development support if hired.
                    @else
                        This candidate may not be suitable for the current position without significant additional assessment.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <hr style="border: none; border-top: 1px solid #dee2e6; margin: 20px 0;">
        <p>
            <strong>Confidentiality Notice:</strong> This report contains confidential psychological assessment information. 
            Distribution should be limited to authorized personnel involved in the hiring decision process.
        </p>
        <p>
            Generated by Psychotest Assessment System on {{ $exportDate->format('d F Y \a\t H:i') }}<br>
            Report ID: PSY-{{ $schedule->id }}-{{ $exportDate->format('Ymd-His') }}
        </p>
        <p style="font-size: 9px; color: #999;">
            This automated report is based on standardized psychological assessment tools. 
            Results should be interpreted by qualified professionals and used as part of a comprehensive evaluation process.
        </p>
    </div>
</body>
</html>