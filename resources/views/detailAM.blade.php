@extends('layouts.main')

@section('title', 'Detail Account Manager')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<style>

        /* Main Content Container */
    .main-content {
        padding: 20px;
        width: calc(100% - 85px);
        margin-left: 85px;
    }

    /* Profile Overview Styling */
    .profile-overview {
        background: linear-gradient(145deg, #ffffff, #f8f9fa);
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        padding: 30px;
        margin-bottom: 25px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        position: relative;
        overflow: hidden;
        border: 1px solid #e9ecef;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .profile-overview::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(to right, #0e223e, #1e3c72);
    }

    .profile-overview:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
    }

    .profile-avatar-container {
        margin-right: 30px;
        position: relative;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #f0f7ff;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }

    .profile-avatar:hover {
        transform: scale(1.05);
    }

    .profile-details {
        flex: 1;
    }

    .profile-name {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1C2955;
        margin-bottom: 15px;
        letter-spacing: -0.5px;
    }

    .profile-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        color: #525f7f;
        font-size: 1rem;
        padding: 10px 18px;
        background-color: #f8f9fa;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s;
        border: 1px solid transparent;
        text-decoration: none !important;
    }

    .meta-item:hover {
        background-color: #f0f7ff;
        transform: translateY(-3px);
        border-color: #d5e3ff;
        box-shadow: 0 8px 15px rgba(28, 41, 85, 0.1);
        text-decoration: none !important;
    }

    .meta-item i {
        margin-right: 10px;
        font-size: 1.3rem;
        color: #1C2955;
    }

    .meta-item span {
        font-weight: 600;
    }

    /* Rankings Container */
    .rankings-container {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
    }

    .ranking-card {
        flex: 1;
        min-width: 200px;
        background: linear-gradient(145deg, #ffffff, #f8f9fa);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        transition: all 0.3s;
        border: 1px solid #e9ecef;
        position: relative;
        overflow: hidden;
        color: inherit;
        text-decoration: none !important;
    }

    .ranking-card:hover {
        transform: translateY(-7px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
        color: inherit !important;
        text-decoration: none !important;
    }

    .ranking-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(28, 41, 85, 0.05) 0%, rgba(239, 242, 247, 0) 100%);
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 1;
        border-radius: 15px;
    }

    .ranking-card:hover::before {
        opacity: 1;
    }

    .ranking-card.global {
        border-left: 5px solid #4c6ef5;
    }

    .ranking-card.witel {
        border-left: 5px solid #3b82f6;
    }

    .ranking-card.division {
        border-left: 5px solid #10b981;
    }

    .ranking-icon {
        width: 70px;
        height: 70px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        color: #1C2955;
        transition: transform 0.3s;
        z-index: 2;
    }

    .ranking-icon img {
        width: 40px !important;
        height: 40px !important;
        transition: transform 0.3s;
    }

    .ranking-card:hover .ranking-icon img {
        transform: scale(1.15);
    }

    .ranking-info {
        flex: 1;
        z-index: 2;
    }

    .ranking-title {
        font-size: 1.1rem;
        font-weight: bold;
        color: #525f7f;
        margin-bottom: 8px;
    }

    .ranking-value {
        font-size: 1.7rem;
        font-weight: 700;
        color: #1C2955;
        line-height: 1.2;
    }

    /* PERBAIKAN: Konsistensi warna badge */
    .rank-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .rank-badge.up {
        background-color: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }

    .rank-badge.down {
        background-color: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }

    .rank-badge.neutral {
        background-color: rgba(107, 114, 128, 0.15);
        color: #6b7280;
    }

    .rank-change-info {
        position: absolute;
        bottom: 15px;
        right: 15px;
        z-index: 2;
        font-size: 0.75rem;
    }

    /* Content Wrapper - FIXED: Adjusting to make tabs flush with edges */
    .content-wrapper {
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        overflow: hidden;
        border: 1px solid #e9ecef;
        margin-bottom: 30px;
        padding: 0;
    }

    /* Tab Navigation - FIXED: Adding rounded corners and flush with edges */
    .tab-navigation {
        display: flex;
        background: linear-gradient(to right, #0e223e, #1e3c72);
        overflow: hidden;
        margin: 0;
        padding: 0;
        width: 100%;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .tab-button {
        flex: 1;
        background-color: transparent;
        border: none;
        padding: 20px;
        font-size: 1.1rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.8);
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    /* FIXED: Adding rounded corners to the tab buttons */
    .tab-button:first-child {
        border-top-left-radius: 15px;
    }

    .tab-button:last-child {
        border-top-right-radius: 15px;
    }

    .tab-button i {
        margin-right: 10px;
        font-size: 1.3rem;
    }

    .tab-button::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 3px;
        background-color: #38bdf8;
        transition: width 0.3s;
    }

    .tab-button:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .tab-button:hover::after {
        width: 30%;
    }

    .tab-button.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
        font-weight: 600;
    }

    .tab-button.active::after {
        width: 100%;
    }

    /* Tab Content - FIXED: Adding bottom padding for spacing */
    .tab-content {
        display: none;
        padding: 30px 30px 40px;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .tab-content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        margin-top: 5px;
        flex-wrap: wrap;
        width: 100%;
    }

    .tab-content-title {
        font-size: 1.4rem;
        font-weight: 600;
        color: #1C2955;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .tab-content-title i {
        margin-right: 12px;
        font-size: 1.6rem;
    }

    /* Filters Container */
    .filters-container {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    /* Bootstrap Select Styling - MODERNIZED */
    .bootstrap-select > .dropdown-toggle {
        height: 40px;
        background: linear-gradient(to right, #0e223e, #1e3c72);
        color: white !important;
        border: none;
        padding: 8px 15px;
        border-radius: 8px !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 500;
        transition: all 0.3s;
        box-shadow: 0 4px 8px rgba(28, 41, 85, 0.2);
    }

    .bootstrap-select > .dropdown-toggle:hover {
        background: linear-gradient(to right, #0c1d34, #172d57);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(28, 41, 85, 0.25);
    }

    .bootstrap-select > .dropdown-toggle:focus {
        outline: none !important;
        box-shadow: 0 0 0 0.25rem rgba(28, 41, 85, 0.25) !important;
        border-radius: 8px !important;
    }

    .bootstrap-select > .dropdown-toggle.bs-placeholder {
        color: white !important;
    }

    .filter-option {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .filter-option-inner {
        width: 100%;
        text-align: center;
    }

    .filter-option-inner-inner {
        text-align: center !important;
        font-weight: 700 !important;
        font-size: 14px;
    }

    .bootstrap-select .dropdown-menu {
        max-width: 100%;
        min-width: 100%;
        margin-top: 8px;
        border: none;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        border-radius: 10px;
        padding: 8px;
    }

    .bootstrap-select .dropdown-item {
        padding: 10px 15px;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .bootstrap-select .dropdown-item:hover {
        background-color: #f0f7ff;
    }

    .bootstrap-select .dropdown-item.active,
    .bootstrap-select .dropdown-item:active {
        background-color: #1C2955;
        border-radius: 6px;
    }

    /* Filter Group */
    .filter-group {
        min-width: 180px;
    }

    /* FIXED: Adding year selector styling */
    .year-selector {
        min-width: 120px;
    }

    /* Data Card */
    .data-card {
        background-color: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s, box-shadow 0.3s;
        border: 1px solid #e9ecef;
    }

    .data-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.1);
    }

    /* Table Styling */
    .table-responsive {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .data-table thead th {
        background: linear-gradient(to right, #f0f7ff, #e6f0ff);
        padding: 16px;
        text-align: left;
        font-weight: 600;
        color: #1C2955;
        border-bottom: 2px solid #e9ecef;
    }

    .data-table tbody tr {
        transition: all 0.2s;
    }

    .data-table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }

    .data-table tbody td {
        padding: 18px 16px;
        border-bottom: 1px solid #e9ecef;
        color: #525f7f;
    }

    .customer-name {
        font-weight: 600;
        color: #1C2955;
    }

    .nipnas {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .achievement-badge {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: transform 0.3s;
    }

    .achievement-badge:hover {
        transform: scale(1.05);
    }

    .badge-success {
        background: linear-gradient(to right, #059669, #10b981);
        color: white;
    }

    .badge-warning {
        background: linear-gradient(to right, #d97706, #f59e0b);
        color: white;
    }

    .badge-danger {
        background: linear-gradient(to right, #dc2626, #ef4444);
        color: white;
    }

    /* Empty State */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        text-align: center;
        background: linear-gradient(145deg, #f8fafc, #f1f5f9);
        border-radius: 12px;
        border: 1px dashed #d0d7de;
    }

    .empty-icon {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 20px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 0.8; }
        50% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(1); opacity: 0.8; }
    }

    .empty-text {
        color: #64748b;
        font-size: 1.1rem;
        max-width: 300px;
        line-height: 1.5;
    }

    /* Insights Section - MODERNIZED */
    .insight-summary-card {
        background: linear-gradient(145deg, #f0f7ff, #e6f0ff);
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        border: 1px solid #c9e0ff;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .insight-summary-card::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(76, 110, 245, 0.1) 0%, rgba(239, 242, 247, 0) 70%);
        border-radius: 50%;
        z-index: 0;
    }

    .insight-summary-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
    }

    .insight-header {
        display: flex;
        align-items: flex-start;
        margin-bottom: 20px;
        color: #1C2955;
        position: relative;
        z-index: 1;
    }

    .insight-header i {
        font-size: 2rem;
        margin-right: 18px;
        color: #4c6ef5;
        margin-top: 0.125rem;
        background: rgba(76, 110, 245, 0.1);
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    .insight-header h4 {
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0;
        margin-top: 0.25rem;
    }

    .insight-body {
        color: #525f7f;
        line-height: 1.8;
        font-size: 1.05rem;
        position: relative;
        z-index: 1;
    }

    /* Total Revenue Summary - MODERNIZED */
    .total-revenue-summary {
        background: linear-gradient(135deg, #1C2955, #253969);
        color: white;
        border-radius: 16px;
        padding: 25px;
        margin-top: 20px;
        margin-bottom: 20px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .total-revenue-summary::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect x="0" y="0" width="100" height="100" fill="none" /><path d="M0 50 L100 80 L100 100 L0 100 Z" fill="rgba(255,255,255,0.05)" /></svg>');
        background-size: cover;
        opacity: 0.1;
    }

    .revenue-icon {
        width: 60px;
        height: 60px;
        background-color: rgba(255,255,255,0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 25px;
        font-size: 1.8rem;
    }

    .revenue-content {
        flex: 1;
    }

    .revenue-label {
        font-size: 1rem;
        font-weight: 500;
        margin-bottom: 8px;
        opacity: 0.9;
    }

    .revenue-value {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .revenue-period {
        font-size: 0.85rem;
        opacity: 0.8;
    }

    /* Insight Metrics - MODERNIZED */
    .insight-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .metric-card {
        background: linear-gradient(145deg, #ffffff, #f8f9fa);
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        border: 1px solid #e9ecef;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .metric-card::before {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 80px;
        height: 80px;
        background: radial-gradient(circle, rgba(28, 41, 85, 0.05) 0%, rgba(239, 242, 247, 0) 70%);
        border-radius: 50%;
    }

    .metric-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
    }

    .metric-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        background: linear-gradient(145deg, rgba(28, 41, 85, 0.1), rgba(76, 110, 245, 0.1));
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 18px;
        font-size: 1.5rem;
        color: #1C2955;
        transition: transform 0.3s;
    }

    .metric-card:hover .metric-icon {
        transform: scale(1.1);
    }

    .metric-content {
        flex: 1;
    }

    .metric-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .metric-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1C2955;
        line-height: 1.3;
    }

    .metric-period {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 5px;
    }

    /* Chart Container - FIXED: Added better spacing and layout */
    .chart-container {
        background-color: white;
        border-radius: 16px;
        padding: 25px 30px 30px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
        overflow: hidden;
    }

    .chart-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 6px;
        background: linear-gradient(to right, #4c6ef5, #3b82f6);
    }

    .chart-container:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
    }

    /* FIXED: Improved chart header spacing and layout */
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .chart-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1C2955;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .chart-title i {
        margin-right: 12px;
        font-size: 1.4rem;
        color: #4c6ef5;
        background: rgba(76, 110, 245, 0.1);
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    /* FIXED: Improved chart filters spacing */
    .chart-filters {
        display: flex;
        gap: 15px;
        align-items: center;
        margin-left: auto;
    }

    .chart-canvas-container {
        height: 450px;
        width: 100%;
        position: relative;
    }

    /* Fix for bootstrap select dropdown shape */
    .dropdown-toggle,
    .btn,
    .selectpicker {
        border-radius: 8px !important;
    }

    .dropdown-toggle:hover,
    .dropdown-toggle:focus {
        border-radius: 8px !important;
    }

    /* Fix for select button spacing */
    .me-2 {
        margin-right: 0.75rem;
    }

    /* Text Helpers */
    .text-success {
        color: #10b981 !important;
    }

    .text-danger {
        color: #ef4444 !important;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .text-center {
        text-align: center;
    }

    /* PERBAIKAN: Konsistensi warna trend */
    .rank-change-detail {
        font-size: 0.75rem;
        margin-top: 3px;
        display: block;
    }

    .rank-change-detail.up {
        color: #10b981;
    }

    .rank-change-detail.down {
        color: #ef4444;
    }

    .rank-change-detail.neutral {
        color: #6b7280;
    }

    /* Responsive Adjustments - FIXED: Improved responsive behavior */
    @media (max-width: 1200px) {
        .insight-metrics {
            grid-template-columns: repeat(2, 1fr);
        }

        .chart-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .chart-filters {
            width: 100%;
            justify-content: flex-end;
        }
    }

    @media (max-width: 992px) {
        .main-content {
            padding: 15px;
        }

        .rankings-container {
            flex-wrap: wrap;
        }

        .ranking-card {
            min-width: calc(33.333% - 14px);
        }

        .profile-overview {
            flex-direction: column;
            text-align: center;
            padding: 25px;
        }

        .profile-avatar-container {
            margin-right: 0;
            margin-bottom: 20px;
        }

        .profile-meta {
            justify-content: center;
        }

        .tab-content-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .chart-canvas-container {
            height: 350px;
        }

        .filters-container {
            width: 100%;
            justify-content: flex-end;
        }
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            width: 100%;
        }

        .rankings-container {
            flex-direction: column;
        }

        .ranking-card {
            width: 100%;
        }

        .tab-navigation {
            flex-direction: column;
        }

        .insight-metrics {
            grid-template-columns: 1fr;
        }

        .chart-canvas-container {
            height: 300px;
        }

        /* FIXED: Better tab navigation on mobile */
        .tab-button:first-child {
            border-top-right-radius: 15px;
        }

        .tab-button:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }
    }

    @media (max-width: 576px) {
        .profile-name {
            font-size: 1.6rem;
        }

        .tab-button {
            padding: 12px;
            font-size: 1rem;
        }

        .tab-content {
            padding: 20px 15px 30px;
        }

        .metric-card {
            flex-direction: column;
            text-align: center;
            padding: 20px;
        }

        .metric-icon {
            margin-right: 0;
            margin-bottom: 15px;
        }

        .chart-canvas-container {
            height: 250px;
        }
    }
    /* Perbaikan untuk menghilangkan garis bawah pada info AM */
    .meta-item, .meta-item:hover {
        text-decoration: none !important;
    }

    /* Memperbaiki posisi dan tampilan badge naik/turun */
    .rank-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Konsistensi warna */
    .rank-badge.up {
        background-color: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }

    .rank-badge.down {
        background-color: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }

    .rank-badge.neutral {
        background-color: rgba(107, 114, 128, 0.15);
        color: #6b7280;
    }

    /* Konsistensi warna di text rank change */
    .rank-change-detail.up {
        color: #10b981;
    }

    .rank-change-detail.down {
        color: #ef4444;
    }

    .rank-change-detail.neutral {
        color: #6b7280;
    }

    /* Rounded tab navigation */
    .tab-navigation {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        overflow: hidden;
    }

    /* Add space after content wrapper */
    .content-wrapper {
        margin-bottom: 30px;
    }

    /* Add space between tab content and next section */
    .tab-content {
        padding-bottom: 40px;
    }

    /* Tab button rounded corners */
    .tab-button:first-child {
        border-top-left-radius: 15px;
    }

    .tab-button:last-child {
        border-top-right-radius: 15px;
    }

    /* Style untuk division tabs */
    .division-tabs {
        display: flex;
        overflow-x: auto;
        padding: 10px 0;
        margin-bottom: 15px;
        border-bottom: 1px solid #e5e7eb;
        gap: 10px;
    }

    .division-tab {
        padding: 8px 16px;
        border-radius: 20px;
        background-color: #f3f4f6;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.3s ease;
    }

    .division-tab.active {
        background-color: #1C2955;
        color: white;
    }

    /* Divisi badge untuk heading */
    .divisi-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        background-color: #e5e7eb;
        font-size: 12px;
        font-weight: 600;
        margin-left: 10px;
    }

    /* === Divisi Badge Color Styling (Disesuaikan dengan warna summary-card) === */
    .divisi-badge.dss {
        background: linear-gradient(135deg, #001F4D 0%, #003366 100%); /* gradasi biru tua */
        color: #FFFFFF; /* teks putih agar kontras */
    }

    .divisi-badge.dps {
        background: linear-gradient(135deg, #66B2FF 0%, #3399FF 100%); /* gradasi biru muda */
        color: #FFFFFF; /* teks putih agar kontras */
    }

    .divisi-badge.dgs {
        background: linear-gradient(135deg, #FFB84D 0%, #FF8C00 100%); /* gradasi dark orange */
        color: #FFFFFF; /* teks putih agar kontras */
    }

    /* Display divisi list di profil - Perbaikan alignment horizontal */
    .meta-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        gap: 8px;
    }

    .meta-item i {
        flex-shrink: 0;
    }

    .meta-item > span {
        flex-shrink: 0;
    }

    .divisi-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        margin: 0;
        padding: 0;
    }

    .divisi-pill {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 14px;
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        white-space: nowrap;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    }

    .divisi-pill:hover {
        background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.12);
    }

    /* === Divisi Pill Color Styling (Disesuaikan dengan warna summary-card) === */
    .divisi-pill.dss {
        background: linear-gradient(135deg, #00264D 0%, #003366 100%); /* gradasi biru tua */
        color: #FFFFFF; /* putih agar kontras */
    }

    .divisi-pill.dps {
        background: linear-gradient(135deg, #66B2FF 0%, #3399FF 100%); /* gradasi biru muda */
        color: #FFFFFF; /* putih agar kontras */
    }

    .divisi-pill.dgs {
        background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%); /* gradasi dark orange */
        color: #FFFFFF; /* putih agar kontras */
    }

    /* Improved profile meta layout - horizontal layout */
    .profile-meta {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 15px;
        align-items: center;
    }

    .meta-item {
        display: flex;
        align-items: center;
        margin-bottom: 0;
        gap: 6px;
        white-space: nowrap;
    }

    .meta-item i {
        flex-shrink: 0;
        font-size: 14px;
        color: #6b7280;
    }

    .meta-item > span {
        flex-shrink: 0;
        font-size: 14px;
        color: #374151;
        font-weight: 500;
    }

    .meta-item.divisi-item {
        align-items: center;
    }

    .divisi-list {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        align-items: center;
        margin: 0;
        padding: 0;
    }

    .divisi-pill {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 12px;
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        font-size: 10px;
        font-weight: 600;
        color: #475569;
        white-space: nowrap;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    }

    /* Perbaikan tampilan card ranking - 3 cards equal width */
    .rankings-container .row {
        margin-right: -10px;
        margin-left: -10px;
    }

    .rankings-container .col-md-4 {
        padding-right: 10px;
        padding-left: 10px;
    }

    .ranking-card {
        height: 100%;
        margin-bottom: 0;
    }

    /* Filter container untuk category filter */
    .category-filter-container {
        background-color: #f8fafc;
        padding: 15px 20px;
        border-radius: 12px;
        margin: 20px 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .category-filters {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    .filter-label {
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .filter-controls {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    /* Perbaikan filter spacing di customer data */
    .filters-container {
        display: flex;
        align-items: center;
        gap: 15px; /* Tambahkan gap yang lebih besar */
        flex-wrap: wrap;
    }

    .filter-group {
        margin-right: 0; /* Hapus margin manual */
    }

    /* Perbaikan chart filters positioning */
    .chart-filters {
        display: flex;
        align-items: center;
        justify-content: center; /* Center the filters */
        gap: 15px;
        flex-wrap: wrap;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .rankings-container .col-md-4 {
            margin-bottom: 15px;
        }

        .category-filters {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-controls {
            justify-content: center;
        }

        .chart-header {
            flex-direction: column;
            align-items: stretch;
        }

        .chart-filters {
            justify-content: center;
        }
    }

    /* Modern select styling improvements */
    .bootstrap-select .dropdown-toggle {
        border-radius: 8px;
        border: 1px solid #d1d5db;
        transition: all 0.2s ease;
    }

    .bootstrap-select .dropdown-toggle:focus {
        border-color: #1C2955;
        box-shadow: 0 0 0 3px rgba(28, 41, 85, 0.1);
    }

    /* Enhanced category badge styling */
    .category-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        border-radius: 25px;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }

    .category-badge.enterprise {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .category-badge.government {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .category-badge.multi {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }

    .category-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    /* Division selector section */
    .division-selector-section {
        background-color: #f8fafc;
        padding: 15px 20px;
        border-radius: 12px;
        margin: 20px 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    .division-selector-label {
        font-weight: 600;
        color: #374151;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Perbaikan lebar ranking cards - pastikan 100% width */
    .rankings-container {
        width: 100%;
        margin: 0;
    }

    .rankings-container .row {
        margin-right: 0;
        margin-left: 0;
        width: 100%;
    }

    .rankings-container .col-md-4 {
        padding-right: 8px;
        padding-left: 8px;
        width: 33.333333%; /* Ensure exact 1/3 width */
        max-width: 33.333333%;
        flex: 0 0 33.333333%;
    }

    /* Mobile responsive untuk ranking cards */
    @media (max-width: 768px) {
        .rankings-container .col-md-4 {
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
            margin-bottom: 15px;
            padding-right: 0;
            padding-left: 0;
        }

        /* Mobile responsive untuk profile meta */
        .profile-meta {
            flex-direction: column;
            gap: 8px;
            align-items: flex-start;
        }

        .meta-item {
            flex-wrap: wrap;
        }

        .meta-item.divisi-item {
            flex-direction: row;
            align-items: center;
        }

        .divisi-list {
            margin-top: 4px;
        }

        .divisi-pill {
            font-size: 11px;
            padding: 3px 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <!-- Profile Overview -->
    <div class="profile-overview">
        <div class="profile-avatar-container">
            <img src="{{ asset($accountManager->user && $accountManager->user->profile_image ? 'storage/'.$accountManager->user->profile_image : 'img/profile.png') }}"
                 class="profile-avatar" alt="{{ $accountManager->nama }}">
        </div>
        <div class="profile-details">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="profile-name mb-0">{{ $accountManager->nama }}</h2>

                <!-- Enhanced Category Badge -->
                @php
                    $badgeClass = 'enterprise';
                    if($amCategory['category'] === 'GOVERNMENT') {
                        $badgeClass = 'government';
                    } elseif($amCategory['category'] === 'MULTI') {
                        $badgeClass = 'multi';
                    }
                @endphp
                <span class="category-badge {{ $badgeClass }}">
                    @if($amCategory['category'] === 'ENTERPRISE')
                        <i class="fas fa-building me-2"></i>
                    @elseif($amCategory['category'] === 'GOVERNMENT')
                        <i class="fas fa-university me-2"></i>
                    @else
                        <i class="fas fa-layer-group me-2"></i>
                    @endif
                    {{ $amCategory['label'] }}
                </span>
            </div>
            <div class="profile-meta">
                <div class="meta-item">
                    <i class="lni lni-id-card"></i>
                    <span>NIK: {{ $accountManager->nik }}</span>
                </div>
                <div class="meta-item">
                    <i class="lni lni-map-marker"></i>
                    <span>WITEL: {{ $accountManager->witel->nama ?? 'N/A' }}</span>
                </div>
                <div class="meta-item divisi-item">
                    <i class="lni lni-network"></i>
                    <span>DIVISI:</span>
                    <div class="divisi-list">
                        @forelse($accountManager->divisis as $divisi)
                            @php
                                $divisiClass = '';
                                switch(strtoupper($divisi->nama)) {
                                    case 'DPS':
                                        $divisiClass = 'dps';
                                        break;
                                    case 'DSS':
                                        $divisiClass = 'dss';
                                        break;
                                    case 'DGS':
                                        $divisiClass = 'dgs';
                                        break;
                                    default:
                                        $divisiClass = '';
                                }
                            @endphp
                            <span class="divisi-pill {{ $divisiClass }}">{{ $divisi->nama }}</span>
                        @empty
                            <span class="text-muted">N/A</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Division Selector Section (moved from profile) -->
    @if(count($divisionRankings) > 0)
    <div class="division-selector-section">
        <div class="division-selector-label">
            <i class="fas fa-layer-group"></i>
            Pilih Divisi untuk Peringkat:
        </div>
        <div class="division-selector">
            <select id="division-ranking-select" class="selectpicker" title="Pilih Divisi" data-style="btn-outline-primary">
                @foreach($divisionRankings as $divisiId => $ranking)
                    <option value="{{ $divisiId }}" {{ $selectedDivisiId == $divisiId ? 'selected' : '' }}>
                        {{ $ranking['nama'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    @endif

    <!-- Category Filter (Only for Multi Division AM) -->
    @if($needsCategoryFilter)
    <div class="category-filter-container">
        <div class="category-filters">
            <div class="filter-label">
                <i class="fas fa-filter me-2"></i>
                Tampilkan Peringkat Sebagai:
            </div>
            <div class="filter-controls">
                <select id="category-filter-select" class="selectpicker" data-style="btn-outline-primary">
                    <option value="enterprise" {{ $selectedCategoryFilter == 'enterprise' ? 'selected' : '' }}>
                        <i class="fas fa-building me-2"></i>Enterprise
                    </option>
                    <option value="government" {{ $selectedCategoryFilter == 'government' ? 'selected' : '' }}>
                        <i class="fas fa-university me-2"></i>Government
                    </option>
                </select>
                <small class="text-muted">Filter ini mempengaruhi peringkat witel Anda</small>
            </div>
        </div>
    </div>
    @endif

    <!-- Rankings - 3 Cards -->
    <div class="rankings-container">
        @php
            $globalRankIcon = "1-10.svg";
            if ($globalRanking['position'] > 10 && $globalRanking['position'] <= 50) {
                $globalRankIcon = "10-50.svg";
            } elseif ($globalRanking['position'] > 50) {
                $globalRankIcon = "up100.svg";
            }

            $witelRankIcon = "1-10.svg";
            if (is_numeric($witelRanking['position'])) {
                if ($witelRanking['position'] > 10 && $witelRanking['position'] <= 50) {
                    $witelRankIcon = "10-50.svg";
                } elseif ($witelRanking['position'] > 50) {
                    $witelRankIcon = "up100.svg";
                }
            }

            // Calculate ranking changes
            $globalChange = isset($globalRanking['position_change']) ? $globalRanking['position_change'] : 0;
            if ($globalChange > 0) {
                $globalChangeClass = 'text-success';
                $globalChangeBadgeClass = 'up';
                $globalChangeIcon = 'lni-arrow-up';
                $globalChangeText = 'naik ' . $globalChange;
                $globalBadgeText = 'Naik ' . $globalChange;
            } elseif ($globalChange < 0) {
                $globalChangeClass = 'text-danger';
                $globalChangeBadgeClass = 'down';
                $globalChangeIcon = 'lni-arrow-down';
                $globalChangeText = 'turun ' . abs($globalChange);
                $globalBadgeText = 'Turun ' . abs($globalChange);
            } else {
                $globalChangeClass = 'text-muted';
                $globalChangeBadgeClass = 'neutral';
                $globalChangeIcon = 'lni-minus';
                $globalChangeText = 'tetap';
                $globalBadgeText = 'Tetap';
            }

            $witelChange = isset($witelRanking['position_change']) ? $witelRanking['position_change'] : 0;
            if ($witelChange > 0) {
                $witelChangeClass = 'text-success';
                $witelChangeBadgeClass = 'up';
                $witelChangeIcon = 'lni-arrow-up';
                $witelChangeText = 'naik ' . $witelChange;
                $witelBadgeText = 'Naik ' . $witelChange;
            } elseif ($witelChange < 0) {
                $witelChangeClass = 'text-danger';
                $witelChangeBadgeClass = 'down';
                $witelChangeIcon = 'lni-arrow-down';
                $witelChangeText = 'turun ' . abs($witelChange);
                $witelBadgeText = 'Turun ' . abs($witelChange);
            } else {
                $witelChangeClass = 'text-muted';
                $witelChangeBadgeClass = 'neutral';
                $witelChangeIcon = 'lni-minus';
                $witelChangeText = 'tetap';
                $witelBadgeText = 'Tetap';
            }

            $currentMonth = date('F');
            $previousMonth = date('F', strtotime('-1 month'));

            // Translate month names
            $monthNames = [
                'January' => 'Januari',
                'February' => 'Februari',
                'March' => 'Maret',
                'April' => 'April',
                'May' => 'Mei',
                'June' => 'Juni',
                'July' => 'Juli',
                'August' => 'Agustus',
                'September' => 'September',
                'October' => 'Oktober',
                'November' => 'November',
                'December' => 'Desember'
            ];

            $currentMonthID = $monthNames[$currentMonth] ?? $currentMonth;
            $previousMonthID = $monthNames[$previousMonth] ?? $previousMonth;
        @endphp

        <div class="row">
            <!-- Card 1: Global Ranking -->
            <div class="col-md-4 mb-3">
                <a href="{{ route('leaderboard') }}" class="ranking-card global">
                    <div class="ranking-icon">
                        <img src="{{ asset('img/' . $globalRankIcon) }}" alt="Peringkat" width="40" height="40">
                    </div>
                    <div class="ranking-info">
                        <div class="ranking-title">Peringkat Global</div>
                        <div class="ranking-value">
                            {{ $globalRanking['position'] }} dari {{ $globalRanking['total'] }}
                            @if ($globalChange != 0)
                                <span class="{{ $globalChangeClass }} ml-2" style="font-size: 14px;">
                                    <i class="lni {{ $globalChangeIcon }}"></i>
                                </span>
                            @endif
                        </div>
                        <span class="rank-change-detail {{ $globalChangeBadgeClass }}">{{ $globalChangeText }} dari {{ $previousMonthID }}</span>
                    </div>
                    @if($globalChange != 0)
                        <div class="rank-badge {{ $globalChangeBadgeClass }}">
                            <i class="lni {{ $globalChangeIcon }}"></i>
                            {{ $globalBadgeText }}
                        </div>
                    @endif
                </a>
            </div>

            <!-- Card 2: Witel Ranking -->
            <div class="col-md-4 mb-3">
                <div class="ranking-card witel">
                    <div class="ranking-icon">
                        <img src="{{ asset('img/' . $witelRankIcon) }}" alt="Peringkat" width="40" height="40">
                    </div>
                    <div class="ranking-info">
                        <div class="ranking-title">
                            Peringkat Witel
                            @if($needsCategoryFilter)
                                <span class="divisi-badge">{{ $witelRanking['category_label'] ?? ucfirst($selectedCategoryFilter) }}</span>
                            @endif
                        </div>
                        <div class="ranking-value">
                            {{ $witelRanking['position'] }} dari {{ $witelRanking['total'] }}
                            @if ($witelChange != 0 && is_numeric($witelRanking['position']))
                                <span class="{{ $witelChangeClass }} ml-2" style="font-size: 14px;">
                                    <i class="lni {{ $witelChangeIcon }}"></i>
                                </span>
                            @endif
                        </div>
                        @if(is_numeric($witelRanking['position']))
                            <span class="rank-change-detail {{ $witelChangeBadgeClass }}">{{ $witelChangeText }} dari {{ $previousMonthID }}</span>
                        @else
                            <span class="rank-change-detail text-muted">belum ada data</span>
                        @endif
                    </div>
                    @if($witelChange != 0 && is_numeric($witelRanking['position']))
                        <div class="rank-badge {{ $witelChangeBadgeClass }}">
                            <i class="lni {{ $witelChangeIcon }}"></i>
                            {{ $witelBadgeText }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card 3: Division Ranking -->
            <div class="col-md-4 mb-3">
                <div class="division-rankings-container">
                    @if(count($divisionRankings) > 0)
                        @foreach($divisionRankings as $divisiId => $ranking)
                            @php
                                $divisiRankIcon = "1-10.svg";
                                if (is_numeric($ranking['position'])) {
                                    if ($ranking['position'] > 10 && $ranking['position'] <= 50) {
                                        $divisiRankIcon = "10-50.svg";
                                    } elseif ($ranking['position'] > 50) {
                                        $divisiRankIcon = "up100.svg";
                                    }
                                }

                                $divisionChange = isset($ranking['position_change']) ? $ranking['position_change'] : 0;
                                if ($divisionChange > 0) {
                                    $divisionChangeClass = 'text-success';
                                    $divisionChangeBadgeClass = 'up';
                                    $divisionChangeIcon = 'lni-arrow-up';
                                    $divisionChangeText = 'naik ' . $divisionChange;
                                    $divisionBadgeText = 'Naik ' . $divisionChange;
                                } elseif ($divisionChange < 0) {
                                    $divisionChangeClass = 'text-danger';
                                    $divisionChangeBadgeClass = 'down';
                                    $divisionChangeIcon = 'lni-arrow-down';
                                    $divisionChangeText = 'turun ' . abs($divisionChange);
                                    $divisionBadgeText = 'Turun ' . abs($divisionChange);
                                } else {
                                    $divisionChangeClass = 'text-muted';
                                    $divisionChangeBadgeClass = 'neutral';
                                    $divisionChangeIcon = 'lni-minus';
                                    $divisionChangeText = 'tetap';
                                    $divisionBadgeText = 'Tetap';
                                }
                            @endphp

                            <div class="ranking-card division division-rank-card" data-divisi-id="{{ $divisiId }}" style="{{ $selectedDivisiId == $divisiId || (empty($selectedDivisiId) && $divisiId == array_key_first($divisionRankings)) ? '' : 'display: none;' }}">
                                <div class="ranking-icon">
                                    <img src="{{ asset('img/' . $divisiRankIcon) }}" alt="Peringkat" width="40" height="40">
                                </div>
                                <div class="ranking-info">
                                <div class="ranking-title">Peringkat Divisi
                                        <span class="divisi-badge {{
                                            strpos(strtolower($ranking['nama'] ?? ''), 'dgs') !== false ? 'dgs' :
                                            (strpos(strtolower($ranking['nama'] ?? ''), 'dps') !== false ? 'dps' :
                                            (strpos(strtolower($ranking['nama'] ?? ''), 'dss') !== false ? 'dss' : ''))
                                        }}">
                                            {{ $ranking['nama'] }}
                                        </span>
                                    </div>                                    <div class="ranking-value">
                                        {{ $ranking['position'] }} dari {{ $ranking['total'] }}
                                        @if ($divisionChange != 0 && is_numeric($ranking['position']))
                                            <span class="{{ $divisionChangeClass }} ml-2" style="font-size: 14px;">
                                                <i class="lni {{ $divisionChangeIcon }}"></i>
                                            </span>
                                        @endif
                                    </div>
                                    @if(is_numeric($ranking['position']))
                                        <span class="rank-change-detail {{ $divisionChangeBadgeClass }}">{{ $divisionChangeText }} dari {{ $previousMonthID }}</span>
                                    @else
                                        <span class="rank-change-detail text-muted">belum ada data</span>
                                    @endif
                                </div>
                                @if($divisionChange != 0 && is_numeric($ranking['position']))
                                    <div class="rank-badge {{ $divisionChangeBadgeClass }}">
                                        <i class="lni {{ $divisionChangeIcon }}"></i>
                                        {{ $divisionBadgeText }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="ranking-card division">
                            <div class="ranking-icon">
                                <img src="{{ asset('img/up100.svg') }}" alt="Peringkat" width="40" height="40">
                            </div>
                            <div class="ranking-info">
                                <div class="ranking-title">Peringkat Divisi</div>
                                <div class="ranking-value">N/A</div>
                                <span class="rank-change-detail text-muted">belum ada data</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Content Tabs -->
    <div class="content-wrapper">
        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-button active" data-tab="customer-data">
                <i class="fas fa-users"></i> Data Customer
            </button>
            <button class="tab-button" data-tab="performance-analysis">
                <i class="fas fa-chart-line"></i> Analisis Performa
            </button>
        </div>

        <!-- Tab Content - Customer Data -->
        <div id="customer-data" class="tab-content active">
            <div class="tab-content-header">
                <div class="tab-content-title">
                    <i class="fas fa-users"></i> Data Customer & Revenue
                </div>

                <div class="filters-container">
                    <!-- Division Selector -->
                    @if(count($accountManager->divisis) > 1)
                    <div class="filter-group">
                        <select id="divisiFilter" class="selectpicker" title="Pilih Divisi">
                            <option value="all">Semua Divisi</option>
                            @foreach($accountManager->divisis as $divisi)
                                <option value="{{ $divisi->id }}" {{ $selectedDivisiId == $divisi->id ? 'selected' : '' }}>
                                    {{ $divisi->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="filter-group">
                        <select id="filterCustomer" class="selectpicker" title="Filter">
                            <option value="all">Semua</option>
                            <option value="highest_achievement">Achievement Tertinggi</option>
                            <option value="highest_revenue">Revenue Tertinggi</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <select name="year" id="year-select" class="selectpicker" data-live-search="true" title="Pilih Tahun">
                            @foreach($yearsList as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Division Tabs for Customer Data -->
            @if(count($accountManager->divisis) > 1)
            <div class="division-tabs" id="customerDivisionTabs">
                <div class="division-tab active" data-divisi-id="all">Semua Divisi</div>
                @foreach($accountManager->divisis as $divisi)
                    <div class="division-tab" data-divisi-id="{{ $divisi->id }}">{{ $divisi->nama }}</div>
                @endforeach
            </div>
            @endif

            <!-- Customer Tables for Each Division -->
            <div id="all-divisions-customer-table" class="data-card division-customer-table active">
                @if(count($customerRevenues) > 0)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>NIPNAS</th>
                                    <th>Divisi</th>
                                    <th>Target Revenue</th>
                                    <th>Real Revenue</th>
                                    <th>Achievement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customerRevenues as $customer)
                                    <tr>
                                        <td>
                                            <div class="customer-name">{{ $customer->nama }}</div>
                                        </td>
                                        <td>
                                            <div class="nipnas">{{ $customer->nipnas }}</div>
                                        </td>
                                        <td>
                                            <div class="customer-divisi">
                                                @php
                                                    $divisiId = $customer->divisi_id ?? null;
                                                    $divisiNama = '';
                                                    if ($divisiId) {
                                                        foreach($accountManager->divisis as $divisi) {
                                                            if ($divisi->id == $divisiId) {
                                                                $divisiNama = $divisi->nama;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                {{ $divisiNama ?: 'Semua' }}
                                            </div>
                                        </td>
                                        <td>Rp {{ number_format($customer->total_target, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($customer->total_revenue, 0, ',', '.') }}</td>
                                        <td>
                                            @php
                                                $achievementClass = 'badge-danger';
                                                if ($customer->achievement >= 100) {
                                                    $achievementClass = 'badge-success';
                                                } elseif ($customer->achievement >= 80) {
                                                    $achievementClass = 'badge-warning';
                                                }
                                            @endphp
                                            <span class="achievement-badge {{ $achievementClass }}">
                                                {{ number_format($customer->achievement, 2, ',', '.') }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <p class="empty-text">Tidak ada data customer untuk tahun {{ $selectedYear }}</p>
                    </div>
                @endif
            </div>

            <!-- Individual Division Customer Tables -->
            @foreach($accountManager->divisis as $divisi)
                <div id="divisi-{{ $divisi->id }}-customer-table" class="data-card division-customer-table" style="display: none;">
                    @if(isset($customerRevenuesByDivisi[$divisi->id]) && count($customerRevenuesByDivisi[$divisi->id]) > 0)
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>NIPNAS</th>
                                        <th>Target Revenue</th>
                                        <th>Real Revenue</th>
                                        <th>Achievement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customerRevenuesByDivisi[$divisi->id] as $customer)
                                        <tr>
                                            <td>
                                                <div class="customer-name">{{ $customer->nama }}</div>
                                            </td>
                                            <td>
                                                <div class="nipnas">{{ $customer->nipnas }}</div>
                                            </td>
                                            <td>Rp {{ number_format($customer->total_target, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($customer->total_revenue, 0, ',', '.') }}</td>
                                            <td>
                                                @php
                                                    $achievementClass = 'badge-danger';
                                                    if ($customer->achievement >= 100) {
                                                        $achievementClass = 'badge-success';
                                                    } elseif ($customer->achievement >= 80) {
                                                        $achievementClass = 'badge-warning';
                                                    }
                                                @endphp
                                                <span class="achievement-badge {{ $achievementClass }}">
                                                    {{ number_format($customer->achievement, 2, ',', '.') }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <p class="empty-text">Tidak ada data customer untuk divisi {{ $divisi->nama }} di tahun {{ $selectedYear }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Tab Content - Performance Analysis -->
        <div id="performance-analysis" class="tab-content">
            <div class="tab-content-header">
                <div class="tab-content-title">
                    <i class="fas fa-chart-line"></i> Analisis Performa & Insight
                </div>

                <!-- Division Selector for Performance -->
                @if(count($accountManager->divisis) > 1)
                <div class="filters-container">
                    <div class="filter-group">
                        <select id="performanceDivisiFilter" class="selectpicker" title="Pilih Divisi">
                            <option value="all">Semua Divisi</option>
                            @foreach($accountManager->divisis as $divisi)
                                <option value="{{ $divisi->id }}" {{ $selectedDivisiId == $divisi->id ? 'selected' : '' }}>
                                    {{ $divisi->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
            </div>

            <!-- Division Tabs for Performance -->
            @if(count($accountManager->divisis) > 1)
            <div class="division-tabs" id="performanceDivisionTabs">
                <div class="division-tab active" data-divisi-id="all">Semua Divisi</div>
                @foreach($accountManager->divisis as $divisi)
                    <div class="division-tab" data-divisi-id="{{ $divisi->id }}">{{ $divisi->nama }}</div>
                @endforeach
            </div>
            @endif

            <!-- Total Revenue Summary - Combined -->
            <div id="all-divisions-performance" class="division-performance active">
                <div class="total-revenue-summary">
                    <div class="revenue-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="revenue-content">
                        <div class="revenue-label">Total Revenue Sepanjang Waktu</div>
                        <div class="revenue-value">
                            @php
                                $totalAllTimeRevenue = $accountManager->revenues->sum('real_revenue');
                                if ($totalAllTimeRevenue >= 1000000000) {
                                    echo 'Rp ' . number_format($totalAllTimeRevenue / 1000000000, 2, ',', '.') . ' Miliar';
                                } elseif ($totalAllTimeRevenue >= 1000000) {
                                    echo 'Rp ' . number_format($totalAllTimeRevenue / 1000000, 2, ',', '.') . ' Juta';
                                } else {
                                    echo 'Rp ' . number_format($totalAllTimeRevenue, 0, ',', '.');
                                }
                            @endphp
                        </div>
                        <div class="revenue-period">Sejak {{ $revenuePeriod['earliest'] }} hingga {{ $revenuePeriod['latest'] }}</div>
                    </div>
                </div>

                <!-- Insights Section - Combined for all divisions -->
                <div class="insight-summary-card">
                    <div class="insight-header">
                        <i class="fas fa-lightbulb"></i>
                        <h4>Ringkasan Performa</h4>
                    </div>
                    <div class="insight-body">
                        <p>{{ $insights['message'] }}</p>

                        <p>Berdasarkan analisis data selama {{ $selectedYear }}, Account Manager <strong>{{ $accountManager->nama }}</strong>
                        menunjukkan achievement yang {{ $insights['avg_achievement'] >= 90 ? 'sangat baik' : ($insights['avg_achievement'] >= 80 ? 'baik' : 'perlu ditingkatkan') }}.
                        Dengan rata-rata achievement <strong>{{ number_format($insights['avg_achievement'], 2) }}%</strong> dan
                        tren performa yang {{ $insights['trend'] == 'up' ? 'meningkat' : ($insights['trend'] == 'down' ? 'menurun' : 'stabil') }}.</p>
                    </div>
                </div>

                <div class="insight-metrics">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">Achievement Tertinggi</div>
                            <div class="metric-value">{{ $insights['best_achievement_month'] ? number_format($insights['best_achievement_month']['achievement'], 2) . '%' : 'N/A' }}</div>
                            <div class="metric-period">
                                @if($insights['best_achievement_month'])
                                    @php
                                        $monthName = $insights['best_achievement_month']['month_name'];
                                        echo isset($monthNames[$monthName]) ? $monthNames[$monthName] : $monthName;
                                    @endphp
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">Revenue Tertinggi</div>
                            <div class="metric-value">
                                @if($insights['best_revenue_month'])
                                    @php
                                        $revenue = $insights['best_revenue_month']['real_revenue'];
                                        if ($revenue >= 1000000000) {
                                            echo 'Rp ' . number_format($revenue / 1000000000, 2, ',', '.') . ' M';
                                        } elseif ($revenue >= 1000000) {
                                            echo 'Rp ' . number_format($revenue / 1000000, 2, ',', '.') . ' Jt';
                                        } else {
                                            echo 'Rp ' . number_format($revenue, 0, ',', '.');
                                        }
                                    @endphp
                                @else
                                    N/A
                                @endif
                            </div>
                            <div class="metric-period">
                                @if($insights['best_revenue_month'])
                                    @php
                                        $monthName = $insights['best_revenue_month']['month_name'];
                                        echo isset($monthNames[$monthName]) ? $monthNames[$monthName] : $monthName;
                                    @endphp
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">Rata-rata Achievement</div>
                            <div class="metric-value">{{ number_format($insights['avg_achievement'], 2) }}%</div>
                            <div class="metric-period">Sepanjang {{ $selectedYear }}</div>
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-icon">
                            @if($insights['trend'] == 'up')
                                <i class="fas fa-arrow-up text-success"></i>
                            @elseif($insights['trend'] == 'down')
                                <i class="fas fa-arrow-down text-danger"></i>
                            @else
                                <i class="fas fa-minus text-muted"></i>
                            @endif
                        </div>
                        <div class="metric-content">
                            <div class="metric-label">Tren Performa</div>
                            <div class="metric-value">
                                @if($insights['trend'] == 'up')
                                    <span class="text-success">Meningkat</span>
                                @elseif($insights['trend'] == 'down')
                                    <span class="text-danger">Menurun</span>
                                @else
                                    <span class="text-muted">Stabil</span>
                                @endif
                            </div>
                            <div class="metric-period">3 bulan terakhir</div>
                        </div>
                    </div>
                </div>

                <!-- Performance Chart for Combined Data -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4 class="chart-title">
                            <i class="fas fa-chart-bar"></i>
                            Grafik Performa Bulanan {{ $selectedYear }}
                        </h4>

                        <div class="chart-filters">
                            <div class="filter-group">
                                <select name="performance_year" id="performance-year-select" class="selectpicker" data-live-search="true" title="Pilih Tahun">
                                    @foreach($yearsList as $year)
                                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="filter-group">
                                <select id="chartType" class="selectpicker" title="Tipe Tampilan">
                                    <option value="combined" selected>Kombinasi</option>
                                    <option value="revenue">Revenue</option>
                                    <option value="achievement">Achievement</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="chart-canvas-container">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Individual Division Performance Sections -->
            @foreach($accountManager->divisis as $divisi)
                <div id="divisi-{{ $divisi->id }}-performance" class="division-performance" style="display: none;">
                    <!-- Total Revenue Summary for individual division -->
                    <div class="total-revenue-summary">
                        <div class="revenue-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="revenue-content">
                            <div class="revenue-label">Total Revenue {{ $divisi->nama }} Sepanjang Waktu</div>
                            <div class="revenue-value">
                                @php
                                    $totalDivisiRevenue = $accountManager->revenues->where('divisi_id', $divisi->id)->sum('real_revenue');
                                    if ($totalDivisiRevenue >= 1000000000) {
                                        echo 'Rp ' . number_format($totalDivisiRevenue / 1000000000, 2, ',', '.') . ' Miliar';
                                    } elseif ($totalDivisiRevenue >= 1000000) {
                                        echo 'Rp ' . number_format($totalDivisiRevenue / 1000000, 2, ',', '.') . ' Juta';
                                    } else {
                                        echo 'Rp ' . number_format($totalDivisiRevenue, 0, ',', '.');
                                    }
                                @endphp
                            </div>
                            <div class="revenue-period">Sejak {{ $revenuePeriod['earliest'] }} hingga {{ $revenuePeriod['latest'] }}</div>
                        </div>
                    </div>

                    <!-- Insights for individual division -->
                    @if(isset($insightsByDivisi[$divisi->id]))
                        <div class="insight-summary-card">
                            <div class="insight-header">
                                <i class="fas fa-lightbulb"></i>
                                <h4>Ringkasan Performa {{ $divisi->nama }}</h4>
                            </div>
                            <div class="insight-body">
                                <p>{{ $insightsByDivisi[$divisi->id]['message'] }}</p>

                                <p>Berdasarkan analisis data selama {{ $selectedYear }}, Account Manager <strong>{{ $accountManager->nama }}</strong>
                                untuk divisi <strong>{{ $divisi->nama }}</strong> menunjukkan achievment yang
                                {{ $insightsByDivisi[$divisi->id]['avg_achievement'] >= 90 ? 'sangat baik' : ($insightsByDivisi[$divisi->id]['avg_achievement'] >= 80 ? 'baik' : 'perlu ditingkatkan') }}.
                                Dengan rata-rata Achievment <strong>{{ number_format($insightsByDivisi[$divisi->id]['avg_achievement'], 2) }}%</strong> dan
                                tren performa yang {{ $insightsByDivisi[$divisi->id]['trend'] == 'up' ? 'meningkat' : ($insightsByDivisi[$divisi->id]['trend'] == 'down' ? 'menurun' : 'stabil') }}.</p>
                            </div>
                        </div>

                        <div class="insight-metrics">
                            <div class="metric-card">
                                <div class="metric-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-label">Achievement Tertinggi</div>
                                    <div class="metric-value">{{ $insightsByDivisi[$divisi->id]['best_achievement_month'] ? number_format($insightsByDivisi[$divisi->id]['best_achievement_month']['achievement'], 2) . '%' : 'N/A' }}</div>
                                    <div class="metric-period">
                                        @if($insightsByDivisi[$divisi->id]['best_achievement_month'])
                                            @php
                                                $monthName = $insightsByDivisi[$divisi->id]['best_achievement_month']['month_name'];
                                                echo isset($monthNames[$monthName]) ? $monthNames[$monthName] : $monthName;
                                            @endphp
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="metric-card">
                                <div class="metric-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-label">Revenue Tertinggi</div>
                                    <div class="metric-value">
                                        @if($insightsByDivisi[$divisi->id]['best_revenue_month'])
                                            @php
                                                $revenue = $insightsByDivisi[$divisi->id]['best_revenue_month']['real_revenue'];
                                                if ($revenue >= 1000000000) {
                                                    echo 'Rp ' . number_format($revenue / 1000000000, 2, ',', '.') . ' M';
                                                } elseif ($revenue >= 1000000) {
                                                    echo 'Rp ' . number_format($revenue / 1000000, 2, ',', '.') . ' Jt';
                                                } else {
                                                    echo 'Rp ' . number_format($revenue, 0, ',', '.');
                                                }
                                            @endphp
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                    <div class="metric-period">
                                        @if($insightsByDivisi[$divisi->id]['best_revenue_month'])
                                            @php
                                                $monthName = $insightsByDivisi[$divisi->id]['best_revenue_month']['month_name'];
                                                echo isset($monthNames[$monthName]) ? $monthNames[$monthName] : $monthName;
                                            @endphp
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="metric-card">
                                <div class="metric-icon">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-label">Rata-rata Achievement</div>
                                    <div class="metric-value">{{ number_format($insightsByDivisi[$divisi->id]['avg_achievement'], 2) }}%</div>
                                    <div class="metric-period">Sepanjang {{ $selectedYear }}</div>
                                </div>
                            </div>

                            <div class="metric-card">
                                <div class="metric-icon">
                                    @if($insightsByDivisi[$divisi->id]['trend'] == 'up')
                                        <i class="fas fa-arrow-up text-success"></i>
                                    @elseif($insightsByDivisi[$divisi->id]['trend'] == 'down')
                                        <i class="fas fa-arrow-down text-danger"></i>
                                    @else
                                        <i class="fas fa-minus text-muted"></i>
                                    @endif
                                </div>
                                <div class="metric-content">
                                    <div class="metric-label">Tren Performa</div>
                                    <div class="metric-value">
                                        @if($insightsByDivisi[$divisi->id]['trend'] == 'up')
                                            <span class="text-success">Meningkat</span>
                                        @elseif($insightsByDivisi[$divisi->id]['trend'] == 'down')
                                            <span class="text-danger">Menurun</span>
                                        @else
                                            <span class="text-muted">Stabil</span>
                                        @endif
                                    </div>
                                    <div class="metric-period">3 bulan terakhir</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="insight-summary-card">
                            <div class="insight-header">
                                <i class="fas fa-lightbulb"></i>
                                <h4>Ringkasan Performa {{ $divisi->nama }}</h4>
                            </div>
                            <div class="insight-body">
                                <p>Belum ada data performa yang tersedia untuk Account Manager ini pada divisi {{ $divisi->nama }}.</p>
                            </div>
                        </div>
                    @endif

                    <!-- Performance Chart for Individual Division -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h4 class="chart-title">
                                <i class="fas fa-chart-bar"></i>
                                Grafik Performa Bulanan {{ $divisi->nama }} {{ $selectedYear }}
                            </h4>

                            <div class="chart-filters">
                                <div class="filter-group">
                                    <select name="divisi_performance_year_{{ $divisi->id }}"
                                            id="divisi-performance-year-select-{{ $divisi->id }}"
                                            class="selectpicker divisi-year-select"
                                            data-divisi-id="{{ $divisi->id }}"
                                            data-live-search="true"
                                            title="Pilih Tahun">
                                        @foreach($yearsList as $year)
                                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <select id="divisi-chartType-{{ $divisi->id }}"
                                            class="selectpicker divisi-chart-type"
                                            data-divisi-id="{{ $divisi->id }}"
                                            title="Tipe Tampilan">
                                        <option value="combined" selected>Kombinasi</option>
                                        <option value="revenue">Revenue</option>
                                        <option value="achievement">Achievement</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="chart-canvas-container">
                            <canvas id="performanceChart-{{ $divisi->id }}"></canvas>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@section('scripts')
<!-- Bootstrap Select -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/js/bootstrap-select.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Bootstrap Select
    $('.selectpicker').selectpicker({
        liveSearch: true,
        liveSearchPlaceholder: 'Cari opsi...',
        size: 5,
        actionsBox: false,
        dropupAuto: false,
        mobile: false
    });

    // Category filter change event (NEW)
    $('#category-filter-select').change(function() {
        if ($(this).val()) {
            let url = "{{ route('account_manager.detail', $accountManager->id) }}?year={{ $selectedYear }}";

            // Add category filter parameter
            url += "&category_filter=" + $(this).val();

            // Add divisi parameter if exists
            const selectedDivisiId = $('#divisiFilter').val();
            if (selectedDivisiId && selectedDivisiId !== 'all') {
                url += "&divisi=" + selectedDivisiId;
            }

            window.location.href = url;
        }
    });

    // Year selector change event - Perbaikan agar keduanya bekerja
    $('#year-select, #performance-year-select').change(function() {
        if ($(this).val()) {
            let url = "{{ route('account_manager.detail', $accountManager->id) }}?year=" + $(this).val();

            // Tambahkan parameter divisi jika ada
            const selectedDivisiId = $('#divisiFilter').val();
            if (selectedDivisiId && selectedDivisiId !== 'all') {
                url += "&divisi=" + selectedDivisiId;
            }

            // Add category filter parameter if exists
            @if($needsCategoryFilter)
            url += "&category_filter={{ $selectedCategoryFilter }}";
            @endif

            window.location.href = url;
        }
    });

    // Division selector change event
    $('#divisiFilter, #performanceDivisiFilter').change(function() {
        if ($(this).val()) {
            let url = "{{ route('account_manager.detail', $accountManager->id) }}?year={{ $selectedYear }}";

            // Jika memilih divisi tertentu
            if ($(this).val() !== 'all') {
                url += "&divisi=" + $(this).val();
            }

            // Add category filter parameter if exists
            @if($needsCategoryFilter)
            url += "&category_filter={{ $selectedCategoryFilter }}";
            @endif

            window.location.href = url;
        }
    });

    // Division ranking selector change event
    $('#division-ranking-select').change(function() {
        const divisiId = $(this).val();

        // Hide all division ranking cards
        $('.division-rank-card').hide();

        // Show selected division ranking card
        $(".division-rank-card[data-divisi-id='" + divisiId + "']").show();
    });

    // Division tabs click event for customer data
    $('#customerDivisionTabs .division-tab').click(function() {
        const divisiId = $(this).data('divisi-id');

        // Remove active class from all tabs and hide all tables
        $('#customerDivisionTabs .division-tab').removeClass('active');
        $('.division-customer-table').hide();

        // Add active class to clicked tab and show corresponding table
        $(this).addClass('active');

        if (divisiId === 'all') {
            $('#all-divisions-customer-table').show();
        } else {
            $('#divisi-' + divisiId + '-customer-table').show();
        }
    });

    // Division tabs click event for performance data
    $('#performanceDivisionTabs .division-tab').click(function() {
        const divisiId = $(this).data('divisi-id');

        // Remove active class from all tabs and hide all tables
        $('#performanceDivisionTabs .division-tab').removeClass('active');
        $('.division-performance').hide();

        // Add active class to clicked tab and show corresponding table
        $(this).addClass('active');

        if (divisiId === 'all') {
            $('#all-divisions-performance').show();
            // Render main performance chart
            setTimeout(function() {
                renderPerformanceChart('combined');
            }, 100);
        } else {
            $('#divisi-' + divisiId + '-performance').show();
            // Render division specific chart
            setTimeout(function() {
                renderDivisiPerformanceChart(divisiId, 'combined');
            }, 100);
        }
    });

    // Bold text for filter inner text
    $('.filter-option-inner-inner').css('font-weight', '700');

    // Tab Navigation
    $('.tab-button').on('click', function() {
        // Remove active class from all buttons and contents
        $('.tab-button').removeClass('active');
        $('.tab-content').removeClass('active');

        // Add active class to clicked button
        $(this).addClass('active');

        // Show corresponding content
        const tabId = $(this).data('tab');
        $('#' + tabId).addClass('active');

        // If switching to performance tab, render chart
        if (tabId === 'performance-analysis') {
            setTimeout(function() {
                const activeTab = $('#performanceDivisionTabs .division-tab.active');
                const divisiId = activeTab.data('divisi-id');

                if (divisiId === 'all' || !divisiId) {
                    renderPerformanceChart('combined');
                } else {
                    renderDivisiPerformanceChart(divisiId, 'combined');
                }
            }, 100);
        }
    });

    // Customer Filters
    $('#filterCustomer').on('changed.bs.select', function() {
        const filterValue = $(this).val();
        const currentDivisiTab = $('#customerDivisionTabs .division-tab.active').data('divisi-id');
        let tableSelector;

        if (currentDivisiTab === 'all' || !currentDivisiTab) {
            tableSelector = '#all-divisions-customer-table .data-table';
        } else {
            tableSelector = '#divisi-' + currentDivisiTab + '-customer-table .data-table';
        }

        if (filterValue === 'all') {
            // Show all rows
            $(tableSelector + ' tbody tr').show();
        } else if (filterValue === 'highest_achievement') {
            // Sort by achievement
            const rows = $(tableSelector + ' tbody tr').toArray();
            rows.sort(function(a, b) {
                const aValue = parseFloat($(a).find('.achievement-badge').text().replace(/\./g, '').replace(',', '.').replace('%', ''));
                const bValue = parseFloat($(b).find('.achievement-badge').text().replace(/\./g, '').replace(',', '.').replace('%', ''));
                return bValue - aValue;
            });

            $(tableSelector + ' tbody').empty().append(rows);
            // Show top 5 only
            $(tableSelector + ' tbody tr').hide().slice(0, 5).show();
        } else if (filterValue === 'highest_revenue') {
            // Sort by revenue
            const rows = $(tableSelector + ' tbody tr').toArray();
            rows.sort(function(a, b) {
                const aValue = parseInt($(a).find('td:eq(3)').text().replace(/[^\d]/g, ''));
                const bValue = parseInt($(b).find('td:eq(3)').text().replace(/[^\d]/g, ''));
                return bValue - aValue;
            });

            $(tableSelector + ' tbody').empty().append(rows);
            // Show top 5 only
            $(tableSelector + ' tbody tr').hide().slice(0, 5).show();
        }
    });

    // Chart Type Selector for main chart
    $('#chartType').on('changed.bs.select', function() {
        renderPerformanceChart($(this).val());
    });

    // Chart Type Selector for division charts
    $('.divisi-chart-type').on('changed.bs.select', function() {
        const divisiId = $(this).data('divisi-id');
        renderDivisiPerformanceChart(divisiId, $(this).val());
    });

    // Division-specific year selector
    $('.divisi-year-select').on('changed.bs.select', function() {
        if ($(this).val()) {
            const divisiId = $(this).data('divisi-id');
            let url = "{{ route('account_manager.detail', $accountManager->id) }}?year=" + $(this).val() + "&divisi=" + divisiId;

            // Add category filter parameter if exists
            @if($needsCategoryFilter)
            url += "&category_filter={{ $selectedCategoryFilter }}";
            @endif

            window.location.href = url;
        }
    });

    // Performance Chart for all divisions combined
    function renderPerformanceChart(type) {
        const ctx = document.getElementById('performanceChart');
        if (!ctx) {
            console.error('Performance chart canvas not found');
            return;
        }

        // Check if chart exists and destroy it
        try {
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }
        } catch (e) {
            console.warn('Error destroying existing chart:', e);
        }

        // Convert month names to Indonesian
        const monthNames = {
            'January': 'Januari',
            'February': 'Februari',
            'March': 'Maret',
            'April': 'April',
            'May': 'Mei',
            'June': 'Juni',
            'July': 'Juli',
            'August': 'Agustus',
            'September': 'September',
            'October': 'Oktober',
            'November': 'November',
            'December': 'Desember'
        };

        // Prepare data
        const monthlyData = @json($monthlyPerformance);

        if (!monthlyData || monthlyData.length === 0) {
            $('.chart-canvas-container').html(
                '<div class="text-center py-5">' +
                '<i class="fas fa-chart-bar fs-1 text-muted mb-3"></i>' +
                '<p class="text-muted">Tidak ada data performa untuk ditampilkan</p>' +
                '</div>'
            );
            return;
        }

        // Translate month names to Indonesian
        const labels = monthlyData.map(item => {
            return monthNames[item.month_name] || item.month_name;
        });

        const revenueData = monthlyData.map(item => item.real_revenue);
        const targetData = monthlyData.map(item => item.target_revenue);
        const achievementData = monthlyData.map(item => item.achievement);

        // Create datasets based on view type
        let datasets = [];

    if (type === 'combined' || type === 'revenue') {
        datasets.push({
            label: 'Real Revenue',
            data: revenueData,
            backgroundColor: 'rgba(46, 204, 113, 0.6)', // hijau
            borderColor: 'rgba(46, 204, 113, 1)',
            borderWidth: 1,
            yAxisID: 'y'
        });

        datasets.push({
            label: 'Target Revenue',
            data: targetData,
            backgroundColor: 'rgba(0, 82, 204, 0.2)',  // biru lebih pekat
            borderColor: 'rgba(0, 82, 204, 1)',
            borderWidth: 1,
            yAxisID: 'y'
        });
    }


        if (type === 'combined' || type === 'achievement') {
            datasets.push({
                label: 'Achievment (%)',
                data: achievementData,
                type: 'line',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: '#1C2955',
                borderWidth: 2,
                pointBackgroundColor: '#1C2955',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#1C2955',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: false,
                tension: 0.3,
                yAxisID: 'y1'
            });
        }

        // Configure scales
        const scales = {};

        if (type === 'combined' || type === 'revenue') {
            scales.y = {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue (Rp)',
                    font: {
                        weight: 'bold'
                    }
                },
                ticks: {
                    callback: function(value) {
                        if (value >= 1000000000) {
                            return 'Rp ' + (value / 1000000000).toFixed(1) + ' M';
                        } else if (value >= 1000000) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                        } else {
                            return 'Rp ' + value;
                        }
                    }
                }
            };
        }

        if (type === 'combined' || type === 'achievement') {
            scales.y1 = {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Achievment (%)',
                    font: {
                        weight: 'bold'
                    }
                },
                grid: {
                    drawOnChartArea: type !== 'combined',
                },
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            };
        }

        // Create new chart
        try {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: scales,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(28, 41, 85, 0.8)',
                            titleFont: {
                                weight: 'bold',
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';

                                    if (label) {
                                        label += ': ';
                                    }

                                    if (context.dataset.yAxisID === 'y1') {
                                        label += context.parsed.y.toFixed(2) + '%';
                                    } else {
                                        const value = context.parsed.y;
                                        if (value >= 1000000000) {
                                            label += 'Rp ' + (value / 1000000000).toFixed(2) + ' M';
                                        } else if (value >= 1000000) {
                                            label += 'Rp ' + (value / 1000000).toFixed(2) + ' Jt';
                                        } else {
                                            label += 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                        }
                                    }

                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Error creating chart:', e);
            $('.chart-canvas-container').html(
                '<div class="alert alert-danger mt-3">' +
                '<i class="fas fa-exclamation-triangle me-2"></i>' +
                'Terjadi kesalahan saat membuat grafik: ' + e.message +
                '</div>'
            );
        }
    }

    // Performance Chart for specific division
    function renderDivisiPerformanceChart(divisiId, type) {
        const ctx = document.getElementById('performanceChart-' + divisiId);
        if (!ctx) {
            console.error('Division performance chart canvas not found for divisi ' + divisiId);
            return;
        }

        // Check if chart exists and destroy it
        try {
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }
        } catch (e) {
            console.warn('Error destroying existing chart:', e);
        }

        // Convert month names to Indonesian
        const monthNames = {
            'January': 'Januari',
            'February': 'Februari',
            'March': 'Maret',
            'April': 'April',
            'May': 'Mei',
            'June': 'Juni',
            'July': 'Juli',
            'August': 'Agustus',
            'September': 'September',
            'October': 'Oktober',
            'November': 'November',
            'December': 'Desember'
        };

        // Prepare data
        const monthlyData = @json($monthlyPerformanceByDivisi);

        if (!monthlyData || !monthlyData[divisiId] || monthlyData[divisiId].length === 0) {
            $('#performanceChart-' + divisiId).parent().html(
                '<div class="text-center py-5">' +
                '<i class="fas fa-chart-bar fs-1 text-muted mb-3"></i>' +
                '<p class="text-muted">Tidak ada data performa untuk ditampilkan</p>' +
                '</div>'
            );
            return;
        }

        // Translate month names to Indonesian
        const labels = monthlyData[divisiId].map(item => {
            return monthNames[item.month_name] || item.month_name;
        });

        const revenueData = monthlyData[divisiId].map(item => item.real_revenue);
        const targetData = monthlyData[divisiId].map(item => item.target_revenue);
        const achievementData = monthlyData[divisiId].map(item => item.achievement);

        // Create datasets based on view type
        let datasets = [];

        if (type === 'combined' || type === 'revenue') {
            datasets.push({
                label: 'Real Revenue',
                data: revenueData,
                backgroundColor: 'rgba(59, 125, 221, 0.6)',
                borderColor: 'rgba(59, 125, 221, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            });

            datasets.push({
                label: 'Target Revenue',
                data: targetData,
                backgroundColor: 'rgba(28, 41, 85, 0.2)',
                borderColor: 'rgba(28, 41, 85, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            });
        }

        if (type === 'combined' || type === 'achievement') {
            datasets.push({
                label: 'Achievment (%)',
                data: achievementData,
                type: 'line',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: '#1C2955',
                borderWidth: 2,
                pointBackgroundColor: '#1C2955',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#1C2955',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: false,
                tension: 0.3,
                yAxisID: 'y1'
            });
        }

        // Configure scales
        const scales = {};

        if (type === 'combined' || type === 'revenue') {
            scales.y = {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue (Rp)',
                    font: {
                        weight: 'bold'
                    }
                },
                ticks: {
                    callback: function(value) {
                        if (value >= 1000000000) {
                            return 'Rp ' + (value / 1000000000).toFixed(1) + ' M';
                        } else if (value >= 1000000) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                        } else {
                            return 'Rp ' + value;
                        }
                    }
                }
            };
        }

        if (type === 'combined' || type === 'achievement') {
            scales.y1 = {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Achievment (%)',
                    font: {
                        weight: 'bold'
                    }
                },
                grid: {
                    drawOnChartArea: type !== 'combined',
                },
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            };
        }

        // Create new chart
        try {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: scales,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(28, 41, 85, 0.8)',
                            titleFont: {
                                weight: 'bold',
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';

                                    if (label) {
                                        label += ': ';
                                    }

                                    if (context.dataset.yAxisID === 'y1') {
                                        label += context.parsed.y.toFixed(2) + '%';
                                    } else {
                                        const value = context.parsed.y;
                                        if (value >= 1000000000) {
                                            label += 'Rp ' + (value / 1000000000).toFixed(2) + ' M';
                                        } else if (value >= 1000000) {
                                            label += 'Rp ' + (value / 1000000).toFixed(2) + ' Jt';
                                        } else {
                                            label += 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                        }
                                    }

                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Error creating chart for division ' + divisiId + ':', e);
            $('#performanceChart-' + divisiId).parent().html(
                '<div class="alert alert-danger mt-3">' +
                '<i class="fas fa-exclamation-triangle me-2"></i>' +
                'Terjadi kesalahan saat membuat grafik: ' + e.message +
                '</div>'
            );
        }
    }

    // Initialize chart if performance tab is active
    if ($('#performance-analysis').hasClass('active')) {
        setTimeout(function() {
            const activeTab = $('#performanceDivisionTabs .division-tab.active');
            const divisiId = activeTab.data('divisi-id');

            if (divisiId === 'all' || !divisiId) {
                renderPerformanceChart('combined');
            } else {
                renderDivisiPerformanceChart(divisiId, 'combined');
            }
        }, 300);
    }

    // Enhance ranking cards with animation
    $('.ranking-card').hover(
        function() {
            $(this).find('.ranking-icon img').css('transform', 'scale(1.15)');
        },
        function() {
            $(this).find('.ranking-icon img').css('transform', 'scale(1)');
        }
    );

    // Add subtle animation to metric cards
    $('.metric-card').hover(
        function() {
            $(this).find('.metric-value').css('transform', 'scale(1.05)');
            $(this).find('.metric-value').css('transition', 'transform 0.3s');
        },
        function() {
            $(this).find('.metric-value').css('transform', 'scale(1)');
        }
    );
});
</script>
@endsection
@endsection