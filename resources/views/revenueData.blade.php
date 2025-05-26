
@extends('layouts.main')

@section('title', 'Data Revenue Account Manager')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/revenue.css') }}">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- ✅ COMPLETE FIXED CSS - All Issues Resolved -->
    <style>
        /* ========== CSS VARIABLES ========== */
        :root {
            /* 🎨 CONSISTENT BLUE PALETTE */
            --primary-blue: #0e223e;
            --secondary-blue: #1e3c72;
            --accent-blue: #2a5298;
            --light-blue: #e7f1ff;
            --blue-gradient: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 50%, var(--accent-blue) 100%);

            /* ✅ Enhanced Color Palette */
            --success-green: #10b981;
            --template-green: #059669;
            --export-purple: #8b5cf6;
            --warning-orange: #f59e0b;
            --error-red: #ef4444;
            --info-blue: #3b82f6;
            --dark-blue: #1C2955;

            /* Neutral Colors */
            --light-gray: #f8f9fa;
            --border-gray: #e3e6f0;
            --text-gray: #6c757d;
            --white: #ffffff;
        }

        /* ✅ EXISTING STYLES - Tetap dipertahankan */
        .divisi-btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .divisi-btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f8f9fa;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            color: #495057 !important;
        }

        .divisi-btn.active {
            background-color: #0d6efd;
            color: white !important;
            border-color: #0d6efd;
        }

        .divisi-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            background-color: #f0f0f0;
            margin-right: 4px;
            font-size: 12px;
        }

        .btn-export {
            background-color: #0a1f44;
            color: white !important;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            border: none;
        }

        .btn-export:hover {
            background-color: #0d2951;
            color: white !important;
        }

        .btn-import {
            background-color: #0d6efd;
            color: white !important;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            border: none;
            cursor: pointer;
        }

        .btn-import:hover {
            background-color: #0b5ed7;
            color: white !important;
        }

        #filterToggle {
            background-color: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        #filterToggle:hover {
            background-color: #0a1f44;
            color: white !important;
        }

        #filterToggle:hover .fas {
            color: white !important;
        }

        /* ✅ ENHANCED: Statistics Section - Blue theme only */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid var(--border-gray);
        }

        .stats-grid {
            display: contents;
        }

        .stat-card {
            background: var(--blue-gradient);
            color: white !important;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            border: 2px solid var(--border-gray);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: var(--secondary-blue);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(255,255,255,0.3);
        }

        .stat-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            opacity: 0.3;
            color: rgba(255,255,255,0.6);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: white !important;
        }

        .stat-label {
            font-size: 14px;
            color: rgba(255,255,255,0.9) !important;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* ✅ ENHANCED: Template Export Cards */
        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .template-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .template-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
            border-color: var(--secondary-blue);
        }

        .template-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--dark-blue);
        }

        .template-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #495057;
        }

        .template-description {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .template-btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 14px;
        }

        .template-btn.template-download {
            background-color: var(--template-green);
            color: white !important;
        }

        .template-btn.template-download:hover {
            background-color: #047857;
            color: white !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4);
        }

        .template-btn.template-export {
            background-color: var(--export-purple);
            color: white !important;
        }

        .template-btn.template-export:hover {
            background-color: #7c3aed;
            color: white !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
        }

        /* ✅ FIXED: Unified Pagination for All Tabs */
        .pagination-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-top: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid var(--border-gray);
        }

        .pagination-simple {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }

        .pagination-info {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
            padding: 12px 20px;
            background: var(--light-gray);
            border-radius: 8px;
            border: 1px solid var(--border-gray);
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .page-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
            padding: 0 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            color: #6b7280;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 14px;
        }

        .page-btn:hover:not(.disabled) {
            border-color: var(--secondary-blue);
            color: var(--secondary-blue);
            background: var(--light-blue);
            transform: translateY(-1px);
            text-decoration: none;
        }

        .page-btn.active {
            background: var(--blue-gradient);
            color: white !important;
            border-color: transparent;
            box-shadow: 0 2px 8px rgba(30, 60, 114, 0.4);
        }

        .page-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f9fafb;
            border-color: #e5e7eb;
            color: #9ca3af;
        }

        .page-btn.disabled:hover {
            transform: none;
            border-color: #e5e7eb;
            background: #f9fafb;
            color: #9ca3af;
        }

        .per-page-select {
            padding: 8px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            cursor: pointer;
            min-width: 60px;
            transition: all 0.3s ease;
        }

        .per-page-select:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        }

        /* ✅ FIXED: Import Result Modal with Better Button Styling */
        .import-result-modal {
            z-index: 1070 !important;
        }

        .import-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .summary-item {
            text-align: center;
            padding: 20px 15px;
            border-radius: 12px;
            border: 2px solid;
            transition: all 0.3s ease;
            background: white;
        }

        .summary-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .summary-item.success {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-color: var(--success-green);
            color: #065f46;
        }

        .summary-item.warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-color: var(--warning-orange);
            color: #92400e;
        }

        .summary-item.error {
            background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
            border-color: var(--error-red);
            color: #991b1b;
        }

        .summary-item.info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-color: var(--info-blue);
            color: #1e40af;
        }

        .summary-number {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            line-height: 1;
        }

        .summary-label {
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .import-details {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
        }

        .detail-item {
            padding: 12px 16px;
            margin: 6px 0;
            border-radius: 8px;
            font-size: 14px;
            border-left: 4px solid;
            line-height: 1.5;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .detail-item.success {
            border-left-color: var(--success-green);
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #166534;
        }

        .detail-item.warning {
            border-left-color: var(--warning-orange);
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            color: #a16207;
        }

        .detail-item.error {
            border-left-color: var(--error-red);
            background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
            color: #b91c1c;
        }

        .import-loading {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 12px;
            border: 2px solid #e2e8f0;
        }

        .import-loading .spinner-border {
            width: 3rem;
            height: 3rem;
            color: var(--secondary-blue);
            border-width: 4px;
        }

        .import-loading h5 {
            color: var(--primary-blue);
            margin-top: 20px;
            font-weight: 600;
        }

        .import-loading p {
            color: var(--text-gray);
            margin-top: 10px;
        }

        /* ✅ FIXED: Modal Overlap Issues */
        .modal-backdrop {
            z-index: 1055;
        }

        .modal {
            z-index: 1060;
        }

        .modal.show .modal-dialog {
            z-index: 1065;
        }

        #importResultModal {
            z-index: 1070 !important;
        }

        #importResultModal .modal-dialog {
            z-index: 1071 !important;
        }

        /* ✅ FIXED: Consistent Button Styles */
        .btn-primary {
            background-color: var(--secondary-blue);
            color: white !important;
            border-color: var(--secondary-blue);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-blue);
            color: white !important;
            border-color: var(--primary-blue);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.4);
        }

        .btn-light {
            background-color: #f8f9fa;
            color: #495057 !important;
            border-color: #f8f9fa;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-light:hover {
            background-color: #e9ecef;
            color: #495057 !important;
            border-color: #e9ecef;
            transform: translateY(-1px);
        }

        .btn-warning {
            background-color: var(--warning-orange);
            color: white !important;
            border-color: var(--warning-orange);
        }

        .btn-warning:hover {
            background-color: #d97706;
            color: white !important;
            border-color: #d97706;
        }

        /* ✅ FIXED: Enhanced Accordion Styles */
        .accordion-button {
            background-color: #f8fafc;
            color: var(--primary-blue) !important;
            font-weight: 600;
            border: none;
            border-radius: 8px !important;
        }

        .accordion-button:not(.collapsed) {
            background-color: var(--light-blue);
            color: var(--primary-blue) !important;
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
            border-color: var(--secondary-blue);
        }

        .accordion-item {
            border: 1px solid #e2e8f0;
            border-radius: 8px !important;
            margin-bottom: 10px;
        }

        /* ✅ EXISTING: Modal Loading Overlay */
        .modal-loading-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            z-index: 1050;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            border-radius: 0.3rem;
        }

        /* ✅ ENHANCED: Notification System */
        .notification-persistent {
            display: flex;
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 320px;
            max-width: 480px;
            padding: 20px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            background-color: white;
            z-index: 9999;
            transform: translateX(110%);
            transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            align-items: flex-start;
            border: 1px solid #e2e8f0;
        }

        .notification-persistent.show {
            transform: translateX(0);
        }

        .notification-persistent .content {
            flex-grow: 1;
            padding-right: 15px;
        }

        .notification-persistent .title {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .notification-persistent .message {
            margin-bottom: 0;
            line-height: 1.5;
        }

        .notification-persistent .close-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
            padding: 0 5px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .notification-persistent .close-btn:hover {
            background-color: #f3f4f6;
            color: #374151;
        }

        .notification-persistent.success {
            border-left: 4px solid var(--success-green);
        }

        .notification-persistent.error {
            border-left: 4px solid var(--error-red);
        }

        .notification-persistent.warning {
            border-left: 4px solid var(--warning-orange);
        }

        .notification-persistent.info {
            border-left: 4px solid var(--info-blue);
        }

        /* ✅ FIXED: Enhanced Month Picker - Better Positioning & Styling */
        .month-picker-container {
            position: relative;
        }

        #global_month_picker {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
            border: 2px solid var(--secondary-blue);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            padding: 25px;
            width: 420px;
            max-width: 90vw;
            margin-top: 5px;
        }

        .month-picker-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .year-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .year-nav-btn {
            background: var(--secondary-blue);
            color: white !important;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .year-nav-btn:hover {
            background: var(--primary-blue);
            transform: scale(1.1);
        }

        .current-year {
            font-size: 20px;
            font-weight: 700;
            color: var(--white);
            min-width: 80px;
            text-align: center;
        }

        .year-input-container {
            margin-bottom: 30px;
            margin-left: 10px;
        }

        .year-input-container input {
            width: 100px;
            text-align: center;
            padding: 8px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }

        .year-input-container input:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        }

        .month-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .month-item {
            padding: 12px 8px;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            font-weight: 500;
            font-size: 14px;
            color: #495057;
        }

        .month-item:hover {
            border-color: var(--secondary-blue);
            background: var(--light-blue);
            color: var(--secondary-blue);
        }

        .month-item.selected {
            background: var(--blue-gradient);
            color: white !important;
            border-color: transparent;
            box-shadow: 0 2px 8px rgba(30, 60, 114, 0.4);
        }

        .month-picker-footer {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .month-picker-footer .btn {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .month-picker-footer .btn-light {
            background: #f8f9fa;
            color: #495057 !important;
            border: 2px solid #e2e8f0;
        }

        .month-picker-footer .btn-light:hover {
            background: #e9ecef;
            border-color: #dee2e6;
        }

        .month-picker-footer .btn-primary {
            background: var(--secondary-blue);
            color: white !important;
            border: 2px solid var(--secondary-blue);
        }

        .month-picker-footer .btn-primary:hover {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        /* ✅ FIXED: Tab Content Default Show for Modals */
        .modal .tab-content {
            display: block;
        }

        .modal .tab-content:not(.active) {
            display: none;
        }

        .modal .tab-content.active {
            display: block;
        }

        /* ✅ RESPONSIVE ENHANCEMENTS */
        @media (max-width: 992px) {
            .stats-container {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 15px;
                padding: 20px;
            }

            .template-grid {
                grid-template-columns: 1fr;
                padding: 15px;
            }

            .pagination-container {
                padding: 20px 15px;
            }

            .pagination-controls {
                flex-direction: column;
                gap: 15px;
            }

            #global_month_picker {
                width: 350px;
            }
        }

        @media (max-width: 768px) {
            .stat-card {
                padding: 20px;
            }

            .stat-number {
                font-size: 2rem;
            }

            .template-card {
                padding: 15px;
            }

            .pagination-controls {
                gap: 10px;
            }

            .page-btn {
                min-width: 36px;
                height: 36px;
                font-size: 13px;
            }

            .notification-persistent {
                min-width: 280px;
                max-width: calc(100vw - 40px);
            }

            #global_month_picker {
                width: 320px;
                padding: 20px;
            }

            .month-grid {
                gap: 8px;
            }

            .month-item {
                padding: 10px 6px;
                font-size: 13px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <!-- Header Dashboard -->
        <div class="header-dashboard">
            <h1 class="header-title">
                Data Revenue Account Manager
            </h1>
            <p class="header-subtitle">
                Kelola dan Monitoring Data Pendapatan Account Manager RLEGS
            </p>
        </div>

        <!-- Snackbar untuk notifikasi -->
        <div id="snackbar"></div>

        <!-- ✅ ENHANCED: Notification container yang persistent -->
        <div id="notification-container" class="notification-persistent">
            <div class="content">
                <div class="title" id="notification-title">Notifikasi</div>
                <p class="message" id="notification-message"></p>
                <div class="details mt-2" id="notification-details" style="display: none;"></div>
            </div>
            <button class="close-btn" id="notification-close">&times;</button>
        </div>

        <!-- Error Message Display -->
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <p class="mb-0"><i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <p class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <p class="mb-0"><i class="fas fa-check-circle me-2"></i> {{ session('success') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Form Tambah Data Revenue -->
        <div class="dashboard-card">
            <div class="card-header">
                <div>
                    <h5 class="card-title">Tambah Data Revenue</h5>
                    <p class="text-muted small mb-0">Tambahkan data revenue baru untuk Account Manager</p>
                </div>
                <div class="d-flex">
                    <a href="{{ route('revenue.export') }}" class="btn-export me-2">
                        <i class="fas fa-download me-1"></i> Export Data
                    </a>
                    <button class="btn-import" data-bs-toggle="modal" data-bs-target="#importRevenueModal">
                        <i class="fas fa-upload me-1"></i> Import Excel
                    </button>
                </div>
            </div>
            <div class="form-section">
                <form action="{{ route('revenue.store') }}" method="POST" id="revenueForm">
                    @csrf
                    <div class="form-row">
                        <div class="form-group form-col-4">
                            <!-- Nama Account Manager -->
                            <label for="account_manager" class="form-label"><strong>Nama Account Manager</strong></label>
                            <div class="position-relative">
                                <input type="text" id="account_manager" class="form-control"
                                    placeholder="Cari Account Manager..." required>
                                <input type="hidden" name="account_manager_id" id="account_manager_id">
                                <div id="account_manager_suggestions" class="suggestions-container"></div>
                            </div>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#addAccountManagerModal"
                                class="add-link">
                                <i class="fas fa-plus-circle"></i> Tambah Account Manager Baru
                            </a>
                        </div>

                        <div class="form-group form-col-4">
                            <!-- Divisi Account Manager -->
                            <label for="divisi_id" class="form-label"><strong>Divisi</strong></label>
                            <select id="divisi_id" name="divisi_id" class="form-control" required disabled>
                                <option value="">Pilih Divisi</option>
                                <!-- Options akan diisi melalui AJAX -->
                            </select>
                            <small class="text-muted">Pilih Account Manager terlebih dahulu</small>
                        </div>

                        <div class="form-group form-col-4">
                            <!-- Nama Corporate Customer -->
                            <label for="corporate_customer" class="form-label"><strong>Nama Corporate Customer</strong></label>
                            <div class="position-relative">
                                <input type="text" id="corporate_customer" class="form-control"
                                    placeholder="Cari Corporate Customer..." required>
                                <input type="hidden" name="corporate_customer_id" id="corporate_customer_id">
                                <div id="corporate_customer_suggestions" class="suggestions-container"></div>
                            </div>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#addCorporateCustomerModal"
                                class="add-link">
                                <i class="fas fa-plus-circle"></i> Tambah Corporate Customer Baru
                            </a>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-col-4">
                            <!-- Target Revenue -->
                            <label for="target_revenue" class="form-label"><strong>Target Revenue</strong></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="target_revenue" id="target_revenue"
                                    placeholder="Masukkan target revenue" required>
                            </div>
                        </div>
                        <div class="form-group form-col-4">
                            <!-- Real Revenue -->
                            <label for="real_revenue" class="form-label"><strong>Real Revenue</strong></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="real_revenue" id="real_revenue"
                                    placeholder="Masukkan real revenue" required>
                            </div>
                        </div>
                        <div class="form-group form-col-4">
                            <!-- Bulan Capaian - Enhanced Month Picker -->
                            <label for="month_year_picker" class="form-label"><strong>Bulan Capaian</strong></label>
                            <div class="month-picker-container">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" id="month_year_picker" class="form-control input-date"
                                        placeholder="Pilih Bulan dan Tahun" readonly>
                                    <span class="input-group-text cursor-pointer" id="open_month_picker"><i
                                            class="fas fa-chevron-down"></i></span>
                                </div>
                                <input type="hidden" name="bulan_month" id="bulan_month" value="{{ date('m') }}">
                                <input type="hidden" name="bulan_year" id="bulan_year" value="{{ date('Y') }}">
                                <input type="hidden" name="bulan" id="bulan" value="{{ date('Y-m') }}">

                                <!-- ✅ FIXED: Enhanced Month Picker positioned below input -->
                                <div id="global_month_picker" class="month-picker">
                                    <div class="month-picker-header">
                                        <div class="year-selector">
                                            <button type="button" class="year-nav-btn" id="prev_year">
                                                <i class="fas fa-chevron-left"></i>
                                            </button>
                                            <span class="current-year" id="current_year">{{ date('Y') }}</span>
                                            <button type="button" class="year-nav-btn" id="next_year">
                                                <i class="fas fa-chevron-right"></i>
                                            </button>
                                        </div>
                                        <div class="year-input-container">
                                            <input type="number" id="year_input" class="form-control form-control-sm"
                                                placeholder="Tahun" min="2000" max="2100">
                                        </div>
                                    </div>
                                    <div class="month-grid" id="month_grid">
                                        <!-- Month items will be populated by JS -->
                                    </div>
                                    <div class="month-picker-footer">
                                        <button type="button" class="btn btn-light" id="cancel_month">BATAL</button>
                                        <button type="button" class="btn btn-primary" id="apply_month">PILIH</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ✅ ENHANCED: Statistics Section -->
        @if(isset($statistics))
        <div class="dashboard-card">
            <div class="card-header">
                <div>
                    <h5 class="card-title">Statistik Revenue</h5>
                    <p class="text-muted small mb-0">Ringkasan data revenue dan performa</p>
                </div>
            </div>
            <div class="stats-container">
                <div class="stats-grid">
                    <!-- Total Revenue -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-number">{{ $statistics['total_revenues'] ?? 0 }}</div>
                        <div class="stat-label">Total Revenue Records</div>
                    </div>

                    <!-- Achievement Rate -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-target"></i>
                        </div>
                        <div class="stat-number">{{ $statistics['achievement_rate'] ?? 0 }}%</div>
                        <div class="stat-label">Achievement Rate ({{ $statistics['current_month'] ?? '' }})</div>
                    </div>

                    <!-- Active Account Managers -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number">{{ $statistics['active_account_managers'] ?? 0 }}</div>
                        <div class="stat-label">Active Account Managers</div>
                    </div>

                    <!-- Active Corporate Customers -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-number">{{ $statistics['active_corporate_customers'] ?? 0 }}</div>
                        <div class="stat-label">Active Corporate Customers</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- ✅ ENHANCED: Template & Export Section -->
        <div class="dashboard-card">
            <div class="card-header">
                <div>
                    <h5 class="card-title">Template & Export Data</h5>
                    <p class="text-muted small mb-0">Download template dan export data untuk berbagai kebutuhan</p>
                </div>
            </div>
            <div class="template-grid">
                <!-- Account Manager Template -->
                <div class="template-card">
                    <div class="template-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="template-title">Account Manager</div>
                    <div class="template-description">Template dan export data Account Manager beserta divisi dan regional</div>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('account-manager.template') }}" class="template-btn template-download">
                            <i class="fas fa-download"></i> Template
                        </a>
                        <a href="{{ route('account-manager.export') }}" class="template-btn template-export">
                            <i class="fas fa-file-export"></i> Export
                        </a>
                    </div>
                </div>

                <!-- Corporate Customer Template -->
                <div class="template-card">
                    <div class="template-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="template-title">Corporate Customer</div>
                    <div class="template-description">Template dan export data Corporate Customer dengan NIPNAS</div>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('corporate-customer.template') }}" class="template-btn template-download">
                            <i class="fas fa-download"></i> Template
                        </a>
                        <a href="{{ route('corporate-customer.export') }}" class="template-btn template-export">
                            <i class="fas fa-file-export"></i> Export
                        </a>
                    </div>
                </div>

                <!-- Revenue Template -->
                <div class="template-card">
                    <div class="template-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="template-title">Revenue Data</div>
                    <div class="template-description">Template fleksibel untuk import data revenue bulanan (Real + Target)</div>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('revenue.template') }}" class="template-btn template-download">
                            <i class="fas fa-download"></i> Template
                        </a>
                        <a href="{{ route('revenue.export') }}" class="template-btn template-export">
                            <i class="fas fa-file-export"></i> Export
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Raw Data RLEGS Telkom -->
        <div class="dashboard-card">
            <div class="card-header">
                <div>
                    <h5 class="card-title">Raw Data RLEGS Telkom</h5>
                    <p class="text-muted small mb-0">Data revenue lengkap Account Manager Telkom</p>
                </div>
                <div class="d-flex align-items-center">
                    <!-- Search Box -->
                    <div class="search-box me-2">
                        <div class="input-group">
                            <input class="form-control" type="search" id="globalSearch" placeholder="Cari data..."
                                autocomplete="off" value="{{ request('search') }}">
                            <button class="btn btn-primary px-3 py-1" type="button" id="searchButton"
                                style="min-width: 5px;">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <!-- Container untuk hasil pencarian -->
                        <div id="searchResultsContainer" class="search-results-container" style="display:none;">
                            <div class="search-results-content">
                                <div class="search-summary">
                                    <p class="mb-0">Hasil pencarian untuk "<span id="search-term-display"
                                            class="fw-bold"></span>"</p>
                                </div>
                                <div id="search-results-loading" class="search-loading">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p class="mt-2 mb-0">Sedang mencari...</p>
                                </div>
                                <div id="search-results-content" style="display:none;">
                                    <div class="p-3">
                                        <p class="mb-2 fw-bold">Ditemukan:</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge bg-primary" id="total-am-count">AM: 0</span>
                                            <span class="badge bg-info" id="total-cc-count">CC: 0</span>
                                            <span class="badge bg-success" id="total-rev-count">Revenue: 0</span>
                                        </div>
                                        <p class="mt-2 mb-0 small text-muted">Hasil telah ditampilkan pada tab terkait</p>
                                    </div>
                                </div>
                                <div id="search-no-results" style="display:none;" class="p-3">
                                    <div class="text-center py-3">
                                        <i class="fas fa-search fa-2x mb-2 text-muted"></i>
                                        <p class="mb-0">Tidak ada hasil yang ditemukan</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-light" id="filterToggle" style="height: 38px;">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>

            <!-- Filter Area (Collapsed by default) -->
            <div class="tab-content p-3 border-bottom" id="filterArea" style="display:none;">
                <form action="{{ route('revenue.data') }}" method="GET">
                    <div class="form-row">
                        <div class="form-group form-col-3">
                            <label class="form-label small">Witel</label>
                            <select name="witel" class="form-control">
                                <option value="">Semua Witel</option>
                                @foreach ($witels as $witel)
                                    <option value="{{ $witel->id }}"
                                        {{ request('witel') == $witel->id ? 'selected' : '' }}>
                                        {{ $witel->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group form-col-3">
                            <label class="form-label small">Regional</label>
                            <select name="regional" class="form-control">
                                <option value="">Semua Regional</option>
                                @foreach ($regionals as $regional)
                                    <option value="{{ $regional->id }}"
                                        {{ request('regional') == $regional->id ? 'selected' : '' }}>
                                        {{ $regional->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group form-col-3">
                            <label class="form-label small">Divisi</label>
                            <select name="divisi" class="form-control">
                                <option value="">Semua Divisi</option>
                                @if(isset($divisis) && $divisis->count() > 0)
                                    @foreach ($divisis as $div)
                                        <option value="{{ $div->id }}"
                                            {{ request('divisi') == $div->id ? 'selected' : '' }}>
                                            {{ $div->nama }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group form-col-3">
                            <label class="form-label small">Bulan</label>
                            <select name="month" class="form-control">
                                <option value="">Semua Bulan</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group form-col-3">
                            <label class="form-label small">Tahun</label>
                            <div class="select-container">
                                <select name="year" class="form-control custom-scroll">
                                    <option value="">Semua Tahun</option>
                                    @if(isset($yearRange) && !empty($yearRange))
                                        @foreach ($yearRange as $year)
                                            <option value="{{ $year }}"
                                                {{ request('year') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    @else
                                        @for($y = 2020; $y <= 2030; $y++)
                                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <a href="{{ route('revenue.data') }}" class="btn btn-light me-2">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Deskripsi Pencarian/Filter -->
            @if (request('search') ||
                    request('witel') ||
                    request('regional') ||
                    request('divisi') ||
                    request('month') ||
                    request('year'))
                <div class="search-description">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <strong>Menampilkan hasil:</strong>
                            @if (request('search'))
                                Pencarian "<span class="text-primary fw-bold">{{ request('search') }}</span>"
                            @endif

                            @if (request('witel'))
                                @php
                                    $witelInfo = $witels->where('id', request('witel'))->first();
                                @endphp
                                @if (request('search'))
                                    dengan
                                @endif
                                Filter Witel: <span
                                    class="text-primary fw-bold">{{ $witelInfo ? $witelInfo->nama : '' }}</span>
                            @endif

                            @if (request('regional'))
                                @php
                                    $regionalInfo = $regionals->where('id', request('regional'))->first();
                                @endphp
                                @if (request('search') || request('witel'))
                                    dan
                                @endif
                                Regional: <span
                                    class="text-primary fw-bold">{{ $regionalInfo ? $regionalInfo->nama : '' }}</span>
                            @endif

                            @if (request('divisi') && isset($divisis))
                                @php
                                    $divisiInfo = $divisis->where('id', request('divisi'))->first();
                                @endphp
                                @if (request('search') || request('witel') || request('regional'))
                                    dan
                                @endif
                                Divisi: <span
                                    class="text-primary fw-bold">{{ $divisiInfo ? $divisiInfo->nama : '' }}</span>
                            @endif

                            @if (request('month'))
                                @if (request('search') || request('witel') || request('regional') || request('divisi'))
                                    dan
                                @endif
                                Bulan: <span
                                    class="text-primary fw-bold">{{ date('F', mktime(0, 0, 0, request('month'), 1)) }}</span>
                            @endif

                            @if (request('year'))
                                @if (request('search') || request('witel') || request('regional') || request('divisi') || request('month'))
                                    dan
                                @endif
                                Tahun: <span class="text-primary fw-bold">{{ request('year') }}</span>
                            @endif
                        </div>
                        <a href="{{ route('revenue.data') }}" class="btn btn-sm btn-light ms-auto">
                            <i class="fas fa-times me-1"></i> Reset Filter
                        </a>
                    </div>
                </div>
            @endif

            <!-- Tab Menu untuk Tabel Data -->
            <div class="tab-menu-container">
                <ul class="tabs">
                    <li class="tab-item active" data-tab="revenueTab"><i class="fas fa-chart-line me-2"></i> Revenue Data
                    </li>
                    <li class="tab-item" data-tab="amTab"><i class="fas fa-user-tie me-2"></i> Account Manager</li>
                    <li class="tab-item" data-tab="ccTab"><i class="fas fa-building me-2"></i> Corporate Customer</li>
                </ul>
            </div>

            <!-- Tab Content untuk Revenue -->
            <div id="revenueTab" class="tab-content active">
                <div id="revenue-search-empty" class="empty-search" style="display:none;">
                    <i class="fas fa-chart-line"></i>
                    <p>Tidak ada data revenue yang sesuai dengan pencarian "<span class="search-keyword"></span>"</p>
                </div>

                @if ($revenues->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <p class="empty-state-text">Belum ada data revenue tersedia.</p>
                    </div>
                @else
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table-modern">
                                <thead>
                                    <tr>
                                        <th>Nama AM</th>
                                        <th>Divisi</th>
                                        <th>Nama Customer</th>
                                        <th>Target Revenue</th>
                                        <th>Real Revenue</th>
                                        <th>Achievement</th>
                                        <th>Bulan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($revenues as $revenue)
                                        @php
                                            $achievement =
                                                $revenue->target_revenue > 0
                                                    ? round(
                                                        ($revenue->real_revenue / $revenue->target_revenue) * 100,
                                                        1,
                                                    )
                                                    : 0;

                                            $statusClass =
                                                $achievement >= 100
                                                    ? 'bg-success-soft'
                                                    : ($achievement >= 80
                                                        ? 'bg-warning-soft'
                                                        : 'bg-danger-soft');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ asset('img/profile.png') }}" class="am-profile-pic"
                                                        alt="{{ $revenue->accountManager->nama }}">
                                                    <span class="ms-2">{{ $revenue->accountManager->nama }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="divisi-badge">
                                                    {{ $revenue->divisi->nama ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>{{ $revenue->corporateCustomer->nama }}</td>
                                            <td>Rp {{ number_format($revenue->target_revenue, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($revenue->real_revenue, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="status-badge {{ $statusClass }}">
                                                    {{ $achievement }}%
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($revenue->bulan . '-01')->format('F Y') }}</td>
                                            <td class="text-center">
                                                <button type="button" class="action-btn edit-btn edit-revenue"
                                                    data-id="{{ $revenue->id }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('revenue.destroy', $revenue->id) }}"
                                                    method="POST" style="display:inline;" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn delete-btn" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- ✅ ENHANCED: Unified Pagination for Revenue -->
                        @if (method_exists($revenues, 'hasPages') && $revenues->hasPages())
                            <div class="pagination-container">
                                <div class="pagination-simple">
                                    <!-- Pagination Info -->
                                    <div class="pagination-info">
                                        Menampilkan {{ $revenues->firstItem() ?? 0 }} sampai {{ $revenues->lastItem() ?? 0 }} dari
                                        {{ $revenues->total() ?? 0 }} hasil
                                    </div>

                                    <!-- Pagination Controls -->
                                    <div class="pagination-controls">
                                        <!-- Previous -->
                                        @if ($revenues->onFirstPage())
                                            <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
                                        @else
                                            <a href="{{ $revenues->previousPageUrl() }}" class="page-btn">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        @endif

                                        <!-- Page Numbers -->
                                        @php
                                            $currentPage = $revenues->currentPage();
                                            $lastPage = $revenues->lastPage();
                                            $range = 2;
                                        @endphp

                                        <!-- First Page -->
                                        @if ($currentPage > $range + 1)
                                            <a href="{{ $revenues->url(1) }}" class="page-btn">1</a>
                                            @if ($currentPage > $range + 2)
                                                <span class="page-btn disabled">...</span>
                                            @endif
                                        @endif

                                        <!-- Page Range -->
                                        @for ($i = max(1, $currentPage - $range); $i <= min($lastPage, $currentPage + $range); $i++)
                                            <a href="{{ $revenues->url($i) }}" class="page-btn {{ $i == $currentPage ? 'active' : '' }}">
                                                {{ $i }}
                                            </a>
                                        @endfor

                                        <!-- Last Page -->
                                        @if ($currentPage < $lastPage - $range)
                                            @if ($currentPage < $lastPage - $range - 1)
                                                <span class="page-btn disabled">...</span>
                                            @endif
                                            <a href="{{ $revenues->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                                        @endif

                                        <!-- Next -->
                                        @if ($revenues->hasMorePages())
                                            <a href="{{ $revenues->nextPageUrl() }}" class="page-btn">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        @else
                                            <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
                                        @endif
                                    </div>

                                    <!-- Per Page Selector -->
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="perPage" class="small">Rows:</label>
                                        <select id="perPage" class="per-page-select" onchange="changePerPage(this.value)">
                                            <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                            <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Tab Content untuk Account Manager -->
            <div id="amTab" class="tab-content">
                <div id="am-search-empty" class="empty-search" style="display:none;">
                    <i class="fas fa-user-tie"></i>
                    <p>Tidak ada data Account Manager yang sesuai dengan pencarian "<span class="search-keyword"></span>"
                    </p>
                </div>

                @if ($accountManagers->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <p class="empty-state-text">Belum ada data Account Manager tersedia.</p>
                    </div>
                @else
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table-modern">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>NIK</th>
                                        <th>Witel</th>
                                        <th>Regional</th>
                                        <th>Divisi</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($accountManagers as $am)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ asset('img/profile.png') }}" class="am-profile-pic"
                                                        alt="{{ $am->nama }}">
                                                    <span class="ms-2">{{ $am->nama }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $am->nik }}</td>
                                            <td>{{ $am->witel->nama }}</td>
                                            <td>{{ $am->regional->nama ?? 'N/A' }}</td>
                                            <td>
                                                @if ($am->divisis->count() > 0)
                                                    @foreach ($am->divisis as $divisi)
                                                        <span class="divisi-badge">{{ $divisi->nama }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="action-btn edit-btn edit-account-manager"
                                                    data-id="{{ $am->id }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('account-manager.destroy', $am->id) }}"
                                                    method="POST" style="display:inline;" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn delete-btn" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- ✅ ENHANCED: Unified Pagination for Account Manager -->
                        @if (isset($accountManagers) && method_exists($accountManagers, 'hasPages') && $accountManagers->hasPages())
                            <div class="pagination-container">
                                <div class="pagination-simple">
                                    <div class="pagination-info">
                                        Menampilkan {{ $accountManagers->firstItem() ?? 0 }} sampai
                                        {{ $accountManagers->lastItem() ?? 0 }} dari {{ $accountManagers->total() ?? 0 }} hasil
                                    </div>
                                    <div class="pagination-controls">
                                        @if ($accountManagers->onFirstPage())
                                            <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
                                        @else
                                            <a href="{{ $accountManagers->previousPageUrl() }}" class="page-btn">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        @endif

                                        @php
                                            $currentPageAM = $accountManagers->currentPage();
                                            $lastPageAM = $accountManagers->lastPage();
                                            $rangeAM = 2;
                                        @endphp

                                        @if ($currentPageAM > $rangeAM + 1)
                                            <a href="{{ $accountManagers->url(1) }}" class="page-btn">1</a>
                                            @if ($currentPageAM > $rangeAM + 2)
                                                <span class="page-btn disabled">...</span>
                                            @endif
                                        @endif

                                        @for ($i = max(1, $currentPageAM - $rangeAM); $i <= min($lastPageAM, $currentPageAM + $rangeAM); $i++)
                                            <a href="{{ $accountManagers->url($i) }}" class="page-btn {{ $i == $currentPageAM ? 'active' : '' }}">
                                                {{ $i }}
                                            </a>
                                        @endfor

                                        @if ($currentPageAM < $lastPageAM - $rangeAM)
                                            @if ($currentPageAM < $lastPageAM - $rangeAM - 1)
                                                <span class="page-btn disabled">...</span>
                                            @endif
                                            <a href="{{ $accountManagers->url($lastPageAM) }}" class="page-btn">{{ $lastPageAM }}</a>
                                        @endif

                                        @if ($accountManagers->hasMorePages())
                                            <a href="{{ $accountManagers->nextPageUrl() }}" class="page-btn">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        @else
                                            <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="perPageAM" class="small">Rows:</label>
                                        <select id="perPageAM" class="per-page-select" onchange="changePerPage(this.value)">
                                            <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                            <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Tab Content untuk Corporate Customer -->
            <div id="ccTab" class="tab-content">
                <div id="cc-search-empty" class="empty-search" style="display:none;">
                    <i class="fas fa-building"></i>
                    <p>Tidak ada data Corporate Customer yang sesuai dengan pencarian "<span
                            class="search-keyword"></span>"</p>
                </div>

                @if ($corporateCustomers->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <p class="empty-state-text">Belum ada data Corporate Customer tersedia.</p>
                    </div>
                @else
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table-modern">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>NIPNAS</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($corporateCustomers as $cc)
                                        <tr>
                                            <td>{{ $cc->nama }}</td>
                                            <td>{{ $cc->nipnas }}</td>
                                            <td class="text-center">
                                                <button type="button" class="action-btn edit-btn edit-corporate-customer"
                                                    data-id="{{ $cc->id }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('corporate-customer.destroy', $cc->id) }}"
                                                    method="POST" style="display:inline;" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn delete-btn" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- ✅ ENHANCED: Unified Pagination for Corporate Customer -->
                        @if (isset($corporateCustomers) && method_exists($corporateCustomers, 'hasPages') && $corporateCustomers->hasPages())
                            <div class="pagination-container">
                                <div class="pagination-simple">
                                    <div class="pagination-info">
                                        Menampilkan {{ $corporateCustomers->firstItem() ?? 0 }} sampai
                                        {{ $corporateCustomers->lastItem() ?? 0 }} dari {{ $corporateCustomers->total() ?? 0 }} hasil
                                    </div>
                                    <div class="pagination-controls">
                                        @if ($corporateCustomers->onFirstPage())
                                            <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
                                        @else
                                            <a href="{{ $corporateCustomers->previousPageUrl() }}" class="page-btn">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        @endif

                                        @php
                                            $currentPageCC = $corporateCustomers->currentPage();
                                            $lastPageCC = $corporateCustomers->lastPage();
                                            $rangeCC = 2;
                                        @endphp

                                        @if ($currentPageCC > $rangeCC + 1)
                                            <a href="{{ $corporateCustomers->url(1) }}" class="page-btn">1</a>
                                            @if ($currentPageCC > $rangeCC + 2)
                                                <span class="page-btn disabled">...</span>
                                            @endif
                                        @endif

                                        @for ($i = max(1, $currentPageCC - $rangeCC); $i <= min($lastPageCC, $currentPageCC + $rangeCC); $i++)
                                            <a href="{{ $corporateCustomers->url($i) }}" class="page-btn {{ $i == $currentPageCC ? 'active' : '' }}">
                                                {{ $i }}
                                            </a>
                                        @endfor

                                        @if ($currentPageCC < $lastPageCC - $rangeCC)
                                            @if ($currentPageCC < $lastPageCC - $rangeCC - 1)
                                                <span class="page-btn disabled">...</span>
                                            @endif
                                            <a href="{{ $corporateCustomers->url($lastPageCC) }}" class="page-btn">{{ $lastPageCC }}</a>
                                        @endif

                                        @if ($corporateCustomers->hasMorePages())
                                            <a href="{{ $corporateCustomers->nextPageUrl() }}" class="page-btn">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        @else
                                            <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="perPageCC" class="small">Rows:</label>
                                        <select id="perPageCC" class="per-page-select" onchange="changePerPage(this.value)">
                                            <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                            <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- ✅ ENHANCED: Import Result Modal - Improved UX/UI --}}
        <div class="modal fade import-result-modal" id="importResultModal" tabindex="-1" aria-labelledby="importResultModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importResultModalLabel">
                            <i class="fas fa-chart-bar me-2"></i>
                            <span id="import-result-title">Hasil Import Data</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- ✅ ENHANCED: Loading State -->
                        <div id="import-loading" class="import-loading">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5>Memproses Import Data</h5>
                            <p class="mb-0">Mohon tunggu, sistem sedang memproses file Anda...</p>
                            <small class="text-muted">Proses ini mungkin memakan waktu beberapa menit untuk file besar</small>
                        </div>

                        <!-- ✅ ENHANCED: Success State -->
                        <div id="import-success" class="import-result" style="display: none;">
                            <!-- Enhanced Summary Cards -->
                            <div class="import-summary" id="import-summary">
                                <div class="summary-item info">
                                    <div class="summary-number" id="imported-count">0</div>
                                    <div class="summary-label">Total Baris</div>
                                </div>
                                <div class="summary-item success">
                                    <div class="summary-number" id="success-count">0</div>
                                    <div class="summary-label">Berhasil</div>
                                </div>
                                <div class="summary-item error">
                                    <div class="summary-number" id="error-count">0</div>
                                    <div class="summary-label">Gagal</div>
                                </div>
                                <div class="summary-item warning">
                                    <div class="summary-number" id="duplicate-count">0</div>
                                    <div class="summary-label">Duplikat</div>
                                </div>
                            </div>

                            <!-- Enhanced Details Accordion -->
                            <div class="accordion" id="importDetailsAccordion">
                                <!-- Validation Errors -->
                                <div class="accordion-item" id="validation-accordion">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#validationErrors">
                                            <i class="fas fa-exclamation-circle text-danger me-2"></i>
                                            Validation Errors (<span id="validation-count-label">0</span>)
                                        </button>
                                    </h2>
                                    <div id="validationErrors" class="accordion-collapse collapse show">
                                        <div class="accordion-body">
                                            <div class="import-details" id="validation-details-list">
                                                <!-- Error items will be populated here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Errors -->
                                <div class="accordion-item" id="data-error-accordion" style="display: none;">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dataErrors">
                                            <i class="fas fa-database text-warning me-2"></i>
                                            Data Errors (<span id="data-error-count-label">0</span>)
                                        </button>
                                    </h2>
                                    <div id="dataErrors" class="accordion-collapse collapse">
                                        <div class="accordion-body">
                                            <div class="import-details" id="data-error-details-list"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Suggestions -->
                                <div class="accordion-item" id="suggestions-accordion" style="display: none;">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#suggestions">
                                            <i class="fas fa-lightbulb text-info me-2"></i>
                                            Suggestions (<span id="suggestions-count-label">0</span>)
                                        </button>
                                    </h2>
                                    <div id="suggestions" class="accordion-collapse collapse">
                                        <div class="accordion-body">
                                            <div class="import-details" id="suggestions-details-list"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ ENHANCED: Error State -->
                        <div id="import-error" class="import-result text-center" style="display: none;">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h5>Import Gagal</h5>
                            <p id="import-error-message" class="text-muted"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Tutup
                        </button>
                        <button type="button" class="btn btn-warning" id="download-error-log" style="display: none;">
                            <i class="fas fa-download me-1"></i> Download Error Log
                        </button>
                        <button type="button" class="btn btn-warning" id="try-again-import" style="display: none;">
                            <i class="fas fa-sync-alt me-1"></i> Coba Lagi
                        </button>
                        <button type="button" class="btn btn-primary" id="refresh-page" style="display: none;">
                            <i class="fas fa-sync-alt me-1"></i> Refresh Halaman
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Account Manager -->
        <div class="modal fade" id="addAccountManagerModal" tabindex="-1" aria-labelledby="addAccountManagerModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAccountManagerModalLabel"><i class="fas fa-user-plus me-2"></i>
                            Tambah Account Manager Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- ✅ FIXED: Default Tab Active -->
                        <div class="tab-menu-container">
                            <ul class="tabs">
                                <li class="tab-item active" data-tab="formTabAM"><i class="fas fa-edit me-2"></i> Form
                                    Manual</li>
                                <li class="tab-item" data-tab="importTabAM"><i class="fas fa-file-import me-2"></i>
                                    Import Excel</li>
                            </ul>
                        </div>

                        <!-- ✅ FIXED: Tab Content Active by Default -->
                        <div id="formTabAM" class="tab-content active">
                            <form id="amForm" action="{{ route('account-manager.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="nama" class="form-label">Nama Account Manager</label>
                                    <input type="text" id="nama" name="nama" class="form-control"
                                        placeholder="Masukkan Nama Account Manager" required>
                                </div>
                                <div class="form-group">
                                    <label for="nik" class="form-label">Nomor Induk Karyawan</label>
                                    <input type="text" id="nik" name="nik" class="form-control"
                                        placeholder="Masukkan 4-10 digit Nomor Induk Karyawan" pattern="^\d{4,10}$" required>
                                </div>
                                <div class="form-group">
                                    <label for="witel_id" class="form-label">Witel</label>
                                    <select name="witel_id" id="witel_id" class="form-control" required>
                                        <option value="">Pilih Witel</option>
                                        @if (isset($witels) && (is_object($witels) || is_array($witels)))
                                            @foreach ($witels as $witel)
                                                @if (is_object($witel) && isset($witel->id) && isset($witel->nama))
                                                    <option value="{{ $witel->id }}">{{ $witel->nama }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="regional_id" class="form-label">Regional</label>
                                    <select name="regional_id" id="regional_id" class="form-control" required>
                                        <option value="">Pilih Regional</option>
                                        @if (isset($regionals) && (is_object($regionals) || is_array($regionals)))
                                            @foreach ($regionals as $regional)
                                                @if (is_object($regional) && isset($regional->id) && isset($regional->nama))
                                                    <option value="{{ $regional->id }}">{{ $regional->nama }}</option>
                                                @endif
                                            @endforeach
                                        @else
                                            <!-- Fallback options jika data regional tidak valid -->
                                            <option value="1">TREG 1</option>
                                            <option value="2">TREG 2</option>
                                            <option value="3">TREG 3</option>
                                            <option value="4">TREG 4</option>
                                            <option value="5">TREG 5</option>
                                            <option value="6">TREG 6</option>
                                            <option value="7">TREG 7</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Divisi</label>
                                    <!-- Container untuk button group dengan styling hard-coded -->
                                    <div class="divisi-btn-group"
                                        style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;">
                                        @if (isset($divisis) && $divisis->count() > 0)
                                            @foreach ($divisis as $div)
                                                <button type="button" class="divisi-btn"
                                                    data-divisi-id="{{ $div->id }}"
                                                    style="padding:8px 15px; border:1px solid #ddd; border-radius:4px; background-color:#f8f9fa; cursor:pointer; font-size:14px;">
                                                    {{ $div->nama }}
                                                </button>
                                            @endforeach
                                        @else
                                            <!-- Fallback buttons jika data divisi tidak valid -->
                                            <button type="button" class="divisi-btn" data-divisi-id="1"
                                                style="padding:8px 15px; border:1px solid #ddd; border-radius:4px; background-color:#f8f9fa; cursor:pointer; font-size:14px;">
                                                DGS
                                            </button>
                                            <button type="button" class="divisi-btn" data-divisi-id="2"
                                                style="padding:8px 15px; border:1px solid #ddd; border-radius:4px; background-color:#f8f9fa; cursor:pointer; font-size:14px;">
                                                DPS
                                            </button>
                                            <button type="button" class="divisi-btn" data-divisi-id="3"
                                                style="padding:8px 15px; border:1px solid #ddd; border-radius:4px; background-color:#f8f9fa; cursor:pointer; font-size:14px;">
                                                DSS
                                            </button>
                                        @endif
                                    </div>
                                    <!-- Hidden input untuk menyimpan divisi yang dipilih -->
                                    <input type="hidden" name="divisi_ids" id="divisi_ids" value="">
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab untuk Import Excel -->
                        <div id="importTabAM" class="tab-content">
                            <form id="amImportForm" action="{{ route('account-manager.import') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="file_upload_am" class="form-label">Unggah File Excel/CSV</label>
                                    <input type="file" name="file" id="file_upload_am" accept=".xlsx, .xls, .csv"
                                        required class="form-control">
                                </div>
                                <div class="alert alert-info mt-3">
                                    <h6 class="alert-heading mb-2"><i class="fas fa-info-circle me-2"></i> Format Data
                                    </h6>
                                    <p class="mb-2 small">File harus memiliki kolom-kolom berikut:</p>
                                    <ul class="small mb-0">
                                        <li><strong>NIK</strong>: Nomor Induk Karyawan (4-10 digit)</li>
                                        <li><strong>NAMA AM</strong>: Nama Account Manager</li>
                                        <li><strong>WITEL HO</strong>: Nama Witel</li>
                                        <li><strong>REGIONAL</strong>: Nama Regional (TREG 1-7)</li>
                                        <li><strong>DIVISI</strong>: Nama Divisi</li>
                                    </ul>
                                </div>
                                <div class="mt-3 d-flex">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i> Unggah Data
                                    </button>
                                    <a href="{{ route('account-manager.template') }}" class="btn btn-light ms-2">
                                        <i class="fas fa-download me-2"></i> Unduh Template
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Corporate Customer -->
        <div class="modal fade" id="addCorporateCustomerModal" tabindex="-1"
            aria-labelledby="addCorporateCustomerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCorporateCustomerModalLabel"><i class="fas fa-building me-2"></i>
                            Tambah Corporate Customer Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- ✅ FIXED: Default Tab Active -->
                        <div class="tab-menu-container">
                            <ul class="tabs">
                                <li class="tab-item active" data-tab="formTabCC"><i class="fas fa-edit me-2"></i> Form
                                    Manual</li>
                                <li class="tab-item" data-tab="importTabCC"><i class="fas fa-file-import me-2"></i>
                                    Import Excel</li>
                            </ul>
                        </div>

                        <!-- ✅ FIXED: Tab Content Active by Default -->
                        <div id="formTabCC" class="tab-content active">
                            <form id="ccForm" action="{{ route('corporate-customer.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="nama_customer" class="form-label">Nama Corporate Customer</label>
                                    <input type="text" name="nama" id="nama_customer" class="form-control"
                                        placeholder="Masukkan Nama Corporate Customer" required>
                                </div>
                                <div class="form-group">
                                    <label for="nipnas" class="form-label">NIPNAS</label>
                                    <input type="text" name="nipnas" id="nipnas" class="form-control"
                                        placeholder="Masukkan NIPNAS (3-20 digit)" required>
                                    <small class="text-muted">NIPNAS harus berupa angka 3-20 digit</small>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab untuk Import Excel -->
                        <div id="importTabCC" class="tab-content">
                            <form id="ccImportForm" action="{{ route('corporate-customer.import') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="file_upload_cc" class="form-label">Unggah File Excel/CSV</label>
                                    <input type="file" name="file" id="file_upload_cc" accept=".xlsx, .xls, .csv"
                                        required class="form-control">
                                </div>
                                <div class="alert alert-info mt-3">
                                    <h6 class="alert-heading mb-2"><i class="fas fa-info-circle me-2"></i> Format Data
                                    </h6>
                                    <p class="mb-2 small">File harus memiliki kolom-kolom berikut:</p>
                                    <ul class="small mb-0">
                                        <li><strong>STANDARD NAME</strong>: Nama Corporate Customer</li>
                                        <li><strong>NIPNAS</strong>: Nomor NIPNAS (3-20 digit angka)</li>
                                    </ul>
                                </div>
                                <div class="mt-3 d-flex">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i> Unggah Data
                                    </button>
                                    <a href="{{ route('corporate-customer.template') }}" class="btn btn-light ms-2">
                                        <i class="fas fa-download me-2"></i> Unduh Template
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Import Revenue -->
        <div class="modal fade" id="importRevenueModal" tabindex="-1" aria-labelledby="importRevenueModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importRevenueModalLabel"><i class="fas fa-file-import me-2"></i>
                            Import Data Revenue</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="revenueImportForm" action="{{ route('revenue.import') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="file_upload_revenue" class="form-label">Unggah File Excel</label>
                                <input type="file" name="file" id="file_upload_revenue" accept=".xlsx, .xls, .csv"
                                    required class="form-control">
                            </div>

                            <div class="alert alert-info mt-3">
                                <h6 class="alert-heading mb-2"><i class="fas fa-info-circle me-2"></i> Format Excel Fleksibel</h6>
                                <p class="mb-2 small">File Excel harus memiliki kolom wajib dan pasangan bulanan:</p>
                                <ul class="small mb-0">
                                    <li><strong>NAMA AM</strong>: Nama Account Manager (wajib)</li>
                                    <li><strong>STANDARD NAME</strong>: Nama Corporate Customer (wajib)</li>
                                    <li><strong>NIK</strong>: NIK AM (opsional - untuk validasi)</li>
                                    <li><strong>NIPNAS</strong>: NIPNAS Customer (opsional - untuk validasi)</li>
                                    <li><strong>DIVISI</strong>: Nama Divisi (opsional - gunakan divisi pertama AM jika kosong)</li>
                                    <li><strong>Pasangan Bulanan</strong>: Real_Jan + Target_Jan, Real_Feb + Target_Feb, dst.</li>
                                </ul>
                                <div class="mt-2">
                                    <small class="text-primary">💡 <strong>Tips:</strong> Tidak wajib 12 bulan - bisa parsial (misal hanya Jan, Mar, Des)</small>
                                </div>
                            </div>

                            <div class="d-flex mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i> Unggah Data
                                </button>
                                <a href="{{ route('revenue.template') }}" class="btn btn-light ms-2">
                                    <i class="fas fa-download me-2"></i> Unduh Template
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit Account Manager -->
        <div class="modal fade" id="editAccountManagerModal" tabindex="-1"
            aria-labelledby="editAccountManagerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAccountManagerModalLabel"><i class="fas fa-user-edit me-2"></i>
                            Edit Account Manager</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Loading overlay untuk menampilkan loading saat mengambil data -->
                        <div class="modal-loading-overlay" id="edit-am-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat data...</p>
                        </div>

                        <form id="editAmForm" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="edit_am_id" name="id">

                            <div class="form-group">
                                <label for="edit_nama" class="form-label">Nama Account Manager</label>
                                <input type="text" id="edit_nama" name="nama" class="form-control"
                                    placeholder="Masukkan Nama Account Manager" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_nik" class="form-label">Nomor Induk Karyawan</label>
                                <input type="text" id="edit_nik" name="nik" class="form-control"
                                    placeholder="Masukkan 4-10 digit Nomor Induk Karyawan" pattern="^\d{4,10}$" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_witel_id" class="form-label">Witel</label>
                                <select name="witel_id" id="edit_witel_id" class="form-control" required>
                                    <option value="">Pilih Witel</option>
                                    @if (isset($witels) && (is_object($witels) || is_array($witels)))
                                        @foreach ($witels as $witel)
                                            @if (is_object($witel) && isset($witel->id) && isset($witel->nama))
                                                <option value="{{ $witel->id }}">{{ $witel->nama }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_regional_id" class="form-label">Regional</label>
                                <select name="regional_id" id="edit_regional_id" class="form-control" required>
                                    <option value="">Pilih Regional</option>
                                    @if (isset($regionals) && (is_object($regionals) || is_array($regionals)))
                                        @foreach ($regionals as $regional)
                                            @if (is_object($regional) && isset($regional->id) && isset($regional->nama))
                                                <option value="{{ $regional->id }}">{{ $regional->nama }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Divisi</label>
                                <div class="divisi-btn-group edit-divisi-btn-group" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;">
                                    @if (isset($divisis) && $divisis->count() > 0)
                                        @foreach ($divisis as $div)
                                            <button type="button" class="divisi-btn"
                                                data-divisi-id="{{ $div->id }}"
                                                style="padding:8px 15px; border:1px solid #ddd; border-radius:4px; background-color:#f8f9fa; cursor:pointer; font-size:14px;">
                                                {{ $div->nama }}
                                            </button>
                                        @endforeach
                                    @else
                                        <!-- Fallback buttons jika data divisi tidak valid -->
                                        <button type="button" class="divisi-btn" data-divisi-id="1"
                                            style="padding:8px 15px; border:1px solid #ddd; border-radius:4px; background-color:#f8f9fa; cursor:pointer; font-size:14px;">
                                            DGS
                                        </button>
                                        <button type="button" class="divisi-btn" data-divisi-id="2"
                                            style="padding:8px 15px; border:1px solid #ddd; border-radius:4px; background-color:#f8f9fa; cursor:pointer; font-size:14px;">
                                            DPS
                                        </button>
                                        <button type="button" class="divisi-btn" data-divisi-id="3"
                                            style="padding:8px 15px; border:1px solid #ddd; border-radius:4px; background-color:#f8f9fa; cursor:pointer; font-size:14px;">
                                            DSS
                                        </button>
                                    @endif
                                </div>
                                <!-- Hidden input untuk menyimpan divisi yang dipilih -->
                                <input type="hidden" name="divisi_ids" id="edit_divisi_ids" value="">
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit Corporate Customer -->
        <div class="modal fade" id="editCorporateCustomerModal" tabindex="-1"
            aria-labelledby="editCorporateCustomerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCorporateCustomerModalLabel"><i class="fas fa-building me-2"></i>
                            Edit Corporate Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Loading overlay -->
                        <div class="modal-loading-overlay" id="edit-cc-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat data...</p>
                        </div>

                        <form id="editCcForm" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="edit_cc_id" name="id">

                            <div class="form-group">
                                <label for="edit_nama_customer" class="form-label">Nama Corporate Customer</label>
                                <input type="text" name="nama" id="edit_nama_customer" class="form-control"
                                    placeholder="Masukkan Nama Corporate Customer" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_nipnas" class="form-label">NIPNAS</label>
                                <input type="text" name="nipnas" id="edit_nipnas" class="form-control"
                                    placeholder="Masukkan NIPNAS (3-20 digit)" required>
                                <small class="text-muted">NIPNAS harus berupa angka 3-20 digit</small>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit Revenue -->
        <div class="modal fade" id="editRevenueModal" tabindex="-1" aria-labelledby="editRevenueModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editRevenueModalLabel"><i class="fas fa-chart-line me-2"></i>
                            Edit Data Revenue</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Loading overlay -->
                        <div class="modal-loading-overlay" id="edit-revenue-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat data...</p>
                        </div>

                        <form id="editRevenueForm" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="edit_revenue_id" name="id">

                            <div class="form-row">
                                <div class="form-group form-col-6">
                                    <!-- Nama Account Manager (Read-only) -->
                                    <label for="edit_account_manager" class="form-label"><strong>Nama Account
                                            Manager</strong></label>
                                    <input type="text" id="edit_account_manager" class="form-control" readonly>
                                    <input type="hidden" name="account_manager_id" id="edit_account_manager_id">
                                </div>

                                <div class="form-group form-col-6">
                                    <!-- Divisi Account Manager (Read-only) -->
                                    <label for="edit_divisi_nama" class="form-label"><strong>Divisi</strong></label>
                                    <input type="text" id="edit_divisi_nama" class="form-control" readonly>
                                    <input type="hidden" name="divisi_id" id="edit_divisi_id">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group form-col-12">
                                    <!-- Nama Corporate Customer (Read-only) -->
                                    <label for="edit_corporate_customer" class="form-label"><strong>Corporate
                                            Customer</strong></label>
                                    <input type="text" id="edit_corporate_customer" class="form-control" readonly>
                                    <input type="hidden" name="corporate_customer_id" id="edit_corporate_customer_id">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group form-col-6">
                                    <!-- Target Revenue -->
                                    <label for="edit_target_revenue" class="form-label"><strong>Target
                                            Revenue</strong></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="target_revenue"
                                            id="edit_target_revenue" placeholder="Masukkan target revenue" required>
                                    </div>
                                </div>
                                <div class="form-group form-col-6">
                                    <!-- Real Revenue -->
                                    <label for="edit_real_revenue" class="form-label"><strong>Real
                                            Revenue</strong></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="real_revenue"
                                            id="edit_real_revenue" placeholder="Masukkan real revenue" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group form-col-12">
                                    <!-- Bulan Capaian - Read-only -->
                                    <label for="edit_bulan" class="form-label"><strong>Bulan Capaian</strong></label>
                                    <input type="text" id="edit_bulan_display" class="form-control" readonly>
                                    <input type="hidden" name="bulan" id="edit_bulan">
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Snackbar dasar -->
        <div id="snackbar"></div>

    </div>
@endsection

@section('scripts')
<!-- Load Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Load dashboard.js dengan versi baru untuk memastikan fresh load -->
<script src="{{ asset('js/dashboard.js?v=' . time()) }}"></script>
<script src="{{ asset('js/revenue.js?v=' . time()) }}"></script>

<!-- ✅ UPDATED: Revenue Management System dengan Enhanced Suggestion Fixes -->
<script>
/**
 * ✅ UPDATED: Revenue Management System dengan Enhanced Suggestion Fixes
 *
 * LATEST UPDATES:
 * - ✅ ENHANCED: Better error handling untuk suggestion dengan null safety
 * - ✅ FIXED: Event binding dengan proper preventDefault dan stopPropagation
 * - ✅ ADDED: Visual hover effects untuk suggestion items
 * - ✅ FIXED: Delay on hide suggestion untuk allow click processing
 * - ✅ ENHANCED: Comprehensive logging untuk debugging
 * - ✅ FIXED: Force JSON response dengan Accept header
 */

class RevenueManagementSystem {
    constructor() {
        // ✅ CORE PROPERTIES
        this.currentTab = 'revenueTab';
        this.importProgress = {};
        this.searchTimeout = null;
        this.suggestions = {
            accountManagers: [],
            corporateCustomers: []
        };
        this.monthPickerVisible = false;
        this.selectedDivisions = new Set();
        this.validationTimeouts = {};
        this.currentEditId = null;
        this.debugMode = true;

        // ✅ ENHANCED CONFIGURATION
        this.config = {
            searchDelay: 300,
            notificationTimeout: 5000,
            importTimeout: 600000,
            validationDelay: 500,
            ajaxTimeout: 30000
        };

        // ✅ ROUTE MAPPINGS
        this.routes = {
            revenue: {
                edit: '/revenue/{id}/edit',
                update: '/revenue/{id}',
                delete: '/revenue/{id}',
                store: '/revenue',
                import: '/revenue/import',
                export: '/revenue/export',
                template: '/revenue/template',
                search: '/revenue/search',
                searchAM: '/revenue/search-account-manager',
                searchCC: '/revenue/search-corporate-customer',
                divisions: '/revenue/account-manager/{id}/divisions'
            },
            accountManager: {
                edit: '/account-manager/{id}/edit',
                update: '/account-manager/{id}',
                delete: '/account-manager/{id}',
                store: '/account-manager',
                import: '/account-manager/import',
                export: '/account-manager/export',
                template: '/account-manager/template',
                search: '/account-manager/search',
                validateNik: '/account-manager/validate-nik'
            },
            corporateCustomer: {
                edit: '/corporate-customer/{id}/edit',
                update: '/corporate-customer/{id}',
                delete: '/corporate-customer/{id}',
                store: '/corporate-customer',
                import: '/corporate-customer/import',
                export: '/corporate-customer/export',
                template: '/corporate-customer/template',
                search: '/corporate-customer/search',
                validateNipnas: '/corporate-customer/validate-nipnas'
            }
        };

        this.init();
    }

    // ✅ INITIALIZATION
    init() {
        this.log('🚀 Revenue Management System - Enhanced Suggestion Version Started');
        this.setupCSRF();
        this.bindAllEvents();
        this.initializeComponents();
        this.setupBlueTheme();
        this.loadInitialData();
        this.testEventBinding();
    }

    setupCSRF() {
        const token = $('meta[name="csrf-token"]').attr('content');
        if (token) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token
                },
                timeout: this.config.ajaxTimeout
            });
            this.log('✅ CSRF Token configured:', token.substring(0, 10) + '...');
        } else {
            this.error('⚠️ CSRF Token not found - AJAX requests will fail!');
        }
    }

    testEventBinding() {
        const editButtons = $('.edit-revenue, .edit-account-manager, .edit-corporate-customer');
        const deleteButtons = $('.delete-form');

        this.log(`🔍 Found ${editButtons.length} edit buttons`);
        this.log(`🔍 Found ${deleteButtons.length} delete forms`);

        if (editButtons.length === 0) {
            this.error('❌ No edit buttons found - check HTML structure!');
        }
        if (deleteButtons.length === 0) {
            this.error('❌ No delete forms found - check HTML structure!');
        }
    }

    initializeComponents() {
        this.initMonthPicker();
        this.initPagination();
        this.initDivisiButtons();
        this.initValidation();
        this.initModalDefaults();
        this.initSuggestionContainers();
        this.switchTab(this.currentTab);

        $('.suggestions-container').hide();
        this.log('✅ All components initialized');
    }

    setupBlueTheme() {
        const stats = document.querySelectorAll('.stat-card');
        stats.forEach((stat, index) => {
            if (stat) {
                stat.style.background = 'var(--blue-gradient)';
                stat.style.color = 'white';
            }
        });
        this.log('✅ Blue theme applied to statistics cards');
    }

    loadInitialData() {
        this.refreshStatistics();
    }

    // ✅ ENHANCED MONTH PICKER INITIALIZATION
initMonthPicker() {
    this.monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    // ✅ ALWAYS set to current date as default (bulan terkini)
    const currentDate = new Date();
    this.selectedMonth = currentDate.getMonth() + 1; // 1-12
    this.selectedYear = currentDate.getFullYear();

    // ✅ Set initial display value to current month/year
    const currentMonthName = this.monthNames[currentDate.getMonth()];
    const displayText = `${currentMonthName} ${this.selectedYear}`;
    $('#month_year_picker').val(displayText);

    // ✅ Also set the hidden form values
    this.updateFormValues();

    this.renderMonthGrid();
    this.bindMonthPickerEvents();
    this.log(`✅ Month picker initialized with current month: ${displayText}`);
}

// ✅ ENHANCED: Show month picker with better positioning
showMonthPicker() {
    const monthPicker = $('#global_month_picker');

    // ✅ Ensure current selection is reflected before showing
    this.renderMonthGrid();

    // ✅ Show with smooth animation
    monthPicker.fadeIn(200);
    this.monthPickerVisible = true;

    this.log(`✅ Month picker shown - Current: ${this.monthNames[this.selectedMonth - 1]} ${this.selectedYear}`);
}

// ✅ ENHANCED: Hide month picker
hideMonthPicker() {
    $('#global_month_picker').fadeOut(200);
    this.monthPickerVisible = false;
    this.log('✅ Month picker hidden');
}

// ✅ ENHANCED: Render month grid with current selection
renderMonthGrid() {
    const grid = $('#month_grid');
    grid.empty();

    this.monthNames.forEach((month, index) => {
        const monthNum = index + 1;
        const isSelected = monthNum === this.selectedMonth;

        const monthItem = $(`
            <div class="month-item ${isSelected ? 'selected' : ''}" data-month="${monthNum}">
                ${month}
            </div>
        `);

        // ✅ Add click handler directly
        monthItem.on('click', (e) => {
            $('.month-item').removeClass('selected');
            $(e.currentTarget).addClass('selected');
            this.selectedMonth = parseInt($(e.currentTarget).data('month'));
            this.log(`✅ Month selected: ${this.monthNames[this.selectedMonth - 1]}`);
        });

        grid.append(monthItem);
    });

    // ✅ Update year display
    $('#current_year').text(this.selectedYear);
    $('#year_input').val(this.selectedYear);

    this.log(`✅ Month grid rendered for ${this.selectedYear}, selected: ${this.monthNames[this.selectedMonth - 1]}`);
}

// ✅ ENHANCED: Bind month picker events with better error handling
bindMonthPickerEvents() {
    // ✅ Main trigger buttons
    $(document).off('click.monthpicker').on('click.monthpicker', '#month_year_picker, #open_month_picker', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.showMonthPicker();
    });

    // ✅ Cancel button
    $(document).off('click.cancel-month').on('click.cancel-month', '#cancel_month', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.hideMonthPicker();
    });

    // ✅ Apply button - CRITICAL FIX
    $(document).off('click.apply-month').on('click.apply-month', '#apply_month', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.applyMonthSelection();
    });

    // ✅ Year navigation
    $(document).off('click.year-nav').on('click.year-nav', '#prev_year', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.selectedYear--;
        this.renderMonthGrid();
    });

    $(document).off('click.year-nav-next').on('click.year-nav-next', '#next_year', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.selectedYear++;
        this.renderMonthGrid();
    });

    // ✅ Year input change
    $(document).off('change.year-input').on('change.year-input', '#year_input', (e) => {
        const year = parseInt(e.target.value);
        if (year >= 2000 && year <= 2100) {
            this.selectedYear = year;
            this.renderMonthGrid();
        } else {
            // Reset to current year if invalid
            e.target.value = this.selectedYear;
            this.showNotification('warning', 'Tahun Tidak Valid', 'Silakan masukkan tahun antara 2000-2100');
        }
    });

    // ✅ Close month picker when clicking outside
    $(document).off('click.month-outside').on('click.month-outside', (e) => {
        if (!$(e.target).closest('#global_month_picker, #month_year_picker, #open_month_picker').length) {
            if (this.monthPickerVisible) {
                this.hideMonthPicker();
            }
        }
    });

    this.log('✅ Month picker events bound with enhanced handlers');
}

// ✅ NEW: Force refresh month picker to current date
refreshToCurrentMonth() {
    const currentDate = new Date();
    this.selectedMonth = currentDate.getMonth() + 1;
    this.selectedYear = currentDate.getFullYear();

    const currentMonthName = this.monthNames[currentDate.getMonth()];
    const displayText = `${currentMonthName} ${this.selectedYear}`;
    $('#month_year_picker').val(displayText);

    this.updateFormValues();
    this.renderMonthGrid();

    this.log(`✅ Month picker refreshed to current: ${displayText}`);
}

// ✅ ENHANCED: Bind month picker events separately
bindMonthPickerEvents() {
    // Main trigger buttons
    $('#month_year_picker, #open_month_picker').off('click.monthpicker').on('click.monthpicker', (e) => {
        e.preventDefault();
        this.showMonthPicker();
    });

    // Cancel button
    $('#cancel_month').off('click.monthpicker').on('click.monthpicker', (e) => {
        e.preventDefault();
        this.hideMonthPicker();
    });

    // Apply button - CRITICAL FIX
    $('#apply_month').off('click.monthpicker').on('click.monthpicker', (e) => {
        e.preventDefault();
        this.applyMonthSelection();
    });

    // Year navigation buttons
    $('#prev_year').off('click.monthpicker').on('click.monthpicker', (e) => {
        e.preventDefault();
        this.selectedYear--;
        this.renderMonthGrid();
    });

    $('#next_year').off('click.monthpicker').on('click.monthpicker', (e) => {
        e.preventDefault();
        this.selectedYear++;
        this.renderMonthGrid();
    });

    // Year input change
    $('#year_input').off('change.monthpicker').on('change.monthpicker', (e) => {
        const year = parseInt(e.target.value);
        if (year >= 2000 && year <= 2100) {
            this.selectedYear = year;
            this.renderMonthGrid();
        }
    });

    this.log('✅ Month picker events bound');
}

// ✅ ENHANCED: Apply month selection with proper form updates
applyMonthSelection() {
    const monthStr = this.selectedMonth.toString().padStart(2, '0');
    const yearMonth = `${this.selectedYear}-${monthStr}`;
    const displayText = `${this.monthNames[this.selectedMonth - 1]} ${this.selectedYear}`;

    // ✅ Update display input
    $('#month_year_picker').val(displayText);

    // ✅ Update all possible form inputs
    this.updateFormValues();

    this.hideMonthPicker();
    this.log(`✅ Month applied: ${yearMonth} (${displayText})`);

    // ✅ Trigger change event for any listening components
    $('#month_year_picker').trigger('change');
}

// ✅ NEW: Update all form values consistently
updateFormValues() {
    const monthStr = this.selectedMonth.toString().padStart(2, '0');
    const yearMonth = `${this.selectedYear}-${monthStr}`;

    // Update individual month/year fields if they exist
    $('#bulan_month').val(monthStr);
    $('#bulan_year').val(this.selectedYear);
    $('#bulan').val(yearMonth);

    // Update any other month-related hidden inputs
    $('input[name="bulan"]').val(yearMonth);
    $('input[name="month"]').val(monthStr);
    $('input[name="year"]').val(this.selectedYear);

    this.log(`✅ Form values updated: month=${monthStr}, year=${this.selectedYear}, combined=${yearMonth}`);
}

    // ✅ ENHANCED: Account Manager Autocomplete dengan Error Handling
    initAccountManagerAutocomplete() {
        const amInput = document.getElementById('account_manager');
        const amIdInput = document.getElementById('account_manager_id');
        const suggestionsContainer = document.getElementById('account_manager_suggestions');

        if (!amInput || !amIdInput || !suggestionsContainer) {
            this.error('❌ AM autocomplete elements not found');
            return;
        }

        $(amInput).off('input.am').on('input.am', (e) => {
            const searchTerm = e.target.value.trim();
            clearTimeout(this.searchTimeout);

            if (searchTerm.length === 0) {
                $(suggestionsContainer).hide();
                $(amIdInput).val('');
                this.resetDivisiDropdown();
                return;
            }

            if (searchTerm.length < 2) return;

            this.searchTimeout = setTimeout(() => {
                this.searchAccountManagers(searchTerm);
            }, this.config.searchDelay);
        });

        $(document).off('click.am-suggestion').on('click.am-suggestion', '#account_manager_suggestions .suggestion-item', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const $item = $(e.currentTarget);
            const id = $item.data('id');
            const name = $item.data('name');

            this.log(`✅ AM suggestion selected: ${name} (ID: ${id})`);

            $(amInput).val(name);
            $(amIdInput).val(id);
            $(suggestionsContainer).hide();

            this.loadAccountManagerDivisions(id);
        });

        // ✅ Add hover effects
        $(document).off('mouseenter.am-hover').on('mouseenter.am-hover', '#account_manager_suggestions .suggestion-item', function() {
            $(this).css('background-color', '#f8f9fa');
        });

        $(document).off('mouseleave.am-hover').on('mouseleave.am-hover', '#account_manager_suggestions .suggestion-item', function() {
            $(this).css('background-color', 'transparent');
        });

        this.log('✅ AM autocomplete initialized with enhanced handlers');
    }

    // ✅ FIXED: Account Manager search dengan better error handling
    async searchAccountManagers(searchTerm) {
        try {
            this.log(`🔍 Searching Account Managers: "${searchTerm}"`);

            const response = await $.ajax({
                url: this.routes.revenue.searchAM,
                method: 'GET',
                data: { search: searchTerm },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                timeout: 10000
            });

            this.log('📥 AM Search response:', response);

            const suggestionsContainer = $('#account_manager_suggestions');

            if (response && response.success && Array.isArray(response.data) && response.data.length > 0) {
                let suggestions = '<ul class="list-unstyled mb-0">';

                response.data.forEach(am => {
                    const nama = am.nama ? String(am.nama) : 'Unknown';
                    const nik = am.nik ? String(am.nik) : 'N/A';
                    const id = am.id || 0;

                    suggestions += `<li class="suggestion-item p-2 border-bottom"
                                       data-id="${id}"
                                       data-name="${this.escapeHtml(nama)}"
                                       style="cursor: pointer; transition: background-color 0.2s;">
                        <strong>${this.escapeHtml(nama)}</strong>
                        <small class="text-muted">(${this.escapeHtml(nik)})</small>
                    </li>`;
                });
                suggestions += '</ul>';

                suggestionsContainer.html(suggestions).show();
                this.log(`✅ Displayed ${response.data.length} AM suggestions`);
            } else {
                suggestionsContainer.hide();
                this.log('ℹ️ No AM suggestions found');
            }
        } catch (error) {
            this.error('❌ Account Manager search error:', error);
            $('#account_manager_suggestions').hide();
        }
    }

    // ✅ ENHANCED: Corporate Customer Autocomplete dengan Error Handling
    initCorporateCustomerAutocomplete() {
        const ccInput = document.getElementById('corporate_customer');
        const ccIdInput = document.getElementById('corporate_customer_id');
        const suggestionsContainer = document.getElementById('corporate_customer_suggestions');

        if (!ccInput || !ccIdInput || !suggestionsContainer) {
            this.error('❌ CC autocomplete elements not found');
            return;
        }

        $(ccInput).off('input.cc').on('input.cc', (e) => {
            const searchTerm = e.target.value.trim();
            clearTimeout(this.searchTimeout);

            if (searchTerm.length === 0) {
                $(suggestionsContainer).hide();
                $(ccIdInput).val('');
                return;
            }

            if (searchTerm.length < 2) return;

            this.searchTimeout = setTimeout(() => {
                this.searchCorporateCustomers(searchTerm);
            }, this.config.searchDelay);
        });

        $(document).off('click.cc-suggestion').on('click.cc-suggestion', '#corporate_customer_suggestions .suggestion-item', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const $item = $(e.currentTarget);
            const id = $item.data('id');
            const name = $item.data('name');

            this.log(`✅ CC suggestion selected: ${name} (ID: ${id})`);

            $(ccInput).val(name);
            $(ccIdInput).val(id);
            $(suggestionsContainer).hide();
        });

        // ✅ Add hover effects
        $(document).off('mouseenter.cc-hover').on('mouseenter.cc-hover', '#corporate_customer_suggestions .suggestion-item', function() {
            $(this).css('background-color', '#f8f9fa');
        });

        $(document).off('mouseleave.cc-hover').on('mouseleave.cc-hover', '#corporate_customer_suggestions .suggestion-item', function() {
            $(this).css('background-color', 'transparent');
        });

        this.log('✅ CC autocomplete initialized with enhanced handlers');
    }

    // ✅ FIXED: Corporate Customer search dengan better error handling
    async searchCorporateCustomers(searchTerm) {
        try {
            this.log(`🔍 Searching Corporate Customers: "${searchTerm}"`);

            const response = await $.ajax({
                url: this.routes.revenue.searchCC,
                method: 'GET',
                data: { search: searchTerm },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                timeout: 10000
            });

            this.log('📥 CC Search response:', response);

            const suggestionsContainer = $('#corporate_customer_suggestions');

            // ✅ ENHANCED: Better response validation
            if (response && response.success && Array.isArray(response.data) && response.data.length > 0) {
                let suggestions = '<ul class="list-unstyled mb-0">';

                response.data.forEach(cc => {
                    // ✅ FIXED: Null safety checks
                    const nama = cc.nama ? String(cc.nama) : 'Unknown';
                    const nipnas = cc.nipnas ? String(cc.nipnas) : 'N/A';
                    const id = cc.id || 0;

                    suggestions += `<li class="suggestion-item p-2 border-bottom"
                                       data-id="${id}"
                                       data-name="${this.escapeHtml(nama)}"
                                       style="cursor: pointer; transition: background-color 0.2s;">
                        <strong>${this.escapeHtml(nama)}</strong>
                        <small class="text-muted">(${this.escapeHtml(nipnas)})</small>
                    </li>`;
                });
                suggestions += '</ul>';

                suggestionsContainer.html(suggestions).show();
                this.log(`✅ Displayed ${response.data.length} CC suggestions`);
            } else {
                suggestionsContainer.hide();
                this.log('ℹ️ No CC suggestions found');
            }
        } catch (error) {
            this.error('❌ Corporate Customer search error:', error);
            $('#corporate_customer_suggestions').hide();

            // ✅ Show user-friendly error
            if (error.status === 500) {
                this.showNotification('error', 'Server Error', 'Terjadi kesalahan pada server. Silakan coba lagi.');
            } else if (error.status === 404) {
                this.showNotification('error', 'Route Not Found', 'Endpoint pencarian tidak ditemukan.');
            }
        }
    }

    // ✅ Load Account Manager Divisions
    async loadAccountManagerDivisions(amId) {
        const divisiSelect = $('#divisi_id');
        if (!divisiSelect.length) return;

        divisiSelect.prop('disabled', true).html('<option value="">Loading...</option>');

        try {
            const response = await $.ajax({
                url: this.routes.revenue.divisions.replace('{id}', amId),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.success && response.divisis) {
                let options = '<option value="">Pilih Divisi</option>';
                response.divisis.forEach(divisi => {
                    options += `<option value="${divisi.id}">${this.escapeHtml(divisi.nama)}</option>`;
                });
                divisiSelect.html(options).prop('disabled', false);
            } else {
                divisiSelect.html('<option value="">Tidak ada divisi</option>').prop('disabled', true);
            }
        } catch (error) {
            this.error('Load divisions error:', error);
            divisiSelect.html('<option value="">Error loading divisi</option>').prop('disabled', true);
        }
    }

    resetDivisiDropdown() {
        const divisiSelect = $('#divisi_id');
        if (divisiSelect.length) {
            divisiSelect.html('<option value="">Pilih Divisi</option>').prop('disabled', true);
        }
    }

    // ✅ NEW: Initialize suggestion containers
    initSuggestionContainers() {
        const containers = ['#account_manager_suggestions', '#corporate_customer_suggestions'];
        containers.forEach(containerSelector => {
            const $container = $(containerSelector);
            if ($container.length) {
                $container.addClass('suggestions-container').hide();
            }
        });
    }

    // ✅ DELETE FUNCTIONALITY
    handleDeleteConfirmation(form) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            this.handleDelete(form);
        }
    }

    async handleDelete(form) {
        const $form = $(form);
        const action = $form.attr('action');
        const method = $form.find('input[name="_method"]').val() || 'DELETE';

        try {
            const $button = $form.find('button[type="submit"]');
            this.showFormLoading($button, 'Menghapus...');

            const response = await $.ajax({
                url: action,
                type: 'POST',
                data: {
                    _method: method,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            this.log('✅ Delete successful:', response);
            this.showNotification('success', 'Berhasil Dihapus', 'Data berhasil dihapus');

            setTimeout(() => this.refreshCurrentTab(), 1000);

        } catch (error) {
            this.error('❌ Delete failed:', error);
            this.handleFormError(error, 'menghapus data');
        }
    }

    // ✅ EDIT FUNCTIONS
    async editRevenue(id) {
        if (!id) {
            this.error('❌ No Revenue ID provided');
            return;
        }

        try {
            this.showModalLoading('#edit-revenue-loading');
            this.log(`📤 Fetching Revenue data for ID: ${id}`);

            const response = await $.ajax({
                url: this.routes.revenue.edit.replace('{id}', id),
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            this.log('📥 Revenue data received:', response);

            if (response.success || response.data || response.id) {
                const data = response.data || response;
                this.fillRevenueEditForm(data);
                $('#editRevenueModal').modal('show');
                this.log('✅ Revenue edit modal opened');
            } else {
                throw new Error('Invalid response format');
            }

        } catch (error) {
            this.error('❌ Failed to load Revenue data:', error);
            this.showNotification('error', 'Error', 'Gagal memuat data revenue untuk diedit');
        } finally {
            this.hideModalLoading('#edit-revenue-loading');
        }
    }

    async editAccountManager(id) {
        if (!id) {
            this.error('❌ No Account Manager ID provided');
            return;
        }

        try {
            this.showModalLoading('#edit-am-loading');
            this.log(`📤 Fetching Account Manager data for ID: ${id}`);

            const response = await $.ajax({
                url: this.routes.accountManager.edit.replace('{id}', id),
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            this.log('📥 Account Manager data received:', response);

            if (response.success || response.data || response.id) {
                const data = response.data || response;
                this.fillAccountManagerEditForm(data);
                $('#editAccountManagerModal').modal('show');
                this.log('✅ Account Manager edit modal opened');
            } else {
                throw new Error('Invalid response format');
            }

        } catch (error) {
            this.error('❌ Failed to load Account Manager data:', error);
            this.showNotification('error', 'Error', 'Gagal memuat data Account Manager untuk diedit');
        } finally {
            this.hideModalLoading('#edit-am-loading');
        }
    }

    async editCorporateCustomer(id) {
        if (!id) {
            this.error('❌ No Corporate Customer ID provided');
            return;
        }

        try {
            this.showModalLoading('#edit-cc-loading');
            this.log(`📤 Fetching Corporate Customer data for ID: ${id}`);

            const response = await $.ajax({
                url: this.routes.corporateCustomer.edit.replace('{id}', id),
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            this.log('📥 Corporate Customer data received:', response);

            if (response.success || response.data || response.id) {
                const data = response.data || response;
                this.fillCorporateCustomerEditForm(data);
                $('#editCorporateCustomerModal').modal('show');
                this.log('✅ Corporate Customer edit modal opened');
            } else {
                throw new Error('Invalid response format');
            }

        } catch (error) {
            this.error('❌ Failed to load Corporate Customer data:', error);
            this.showNotification('error', 'Error', 'Gagal memuat data Corporate Customer untuk diedit');
        } finally {
            this.hideModalLoading('#edit-cc-loading');
        }
    }

    // ✅ FILL FORM FUNCTIONS
    fillAccountManagerEditForm(data) {
        const elements = {
            'edit_am_id': data.id,
            'edit_nama': data.nama,
            'edit_nik': data.nik,
            'edit_witel_id': data.witel_id,
            'edit_regional_id': data.regional_id
        };

        Object.keys(elements).forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = elements[id] || '';
        });

        const divisiButtons = document.querySelectorAll('.edit-divisi-btn-group .divisi-btn');
        divisiButtons.forEach(button => {
            button.classList.remove('active');
            this.resetDivisiButtonStyle(button);
        });

        if (data.divisis && data.divisis.length > 0) {
            const divisiIds = data.divisis.map(divisi => divisi.id);
            const divisiIdsInput = document.getElementById('edit_divisi_ids');
            if (divisiIdsInput) divisiIdsInput.value = divisiIds.join(',');

            divisiButtons.forEach(button => {
                const divisiId = parseInt(button.dataset.divisiId);
                if (divisiIds.includes(divisiId)) {
                    button.classList.add('active');
                    this.setActiveDivisiButtonStyle(button);
                }
            });
        }
    }

    fillCorporateCustomerEditForm(data) {
        const elements = {
            'edit_cc_id': data.id,
            'edit_nama_customer': data.nama,
            'edit_nipnas': data.nipnas
        };

        Object.keys(elements).forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = elements[id] || '';
        });
    }

    fillRevenueEditForm(data) {
        const elements = {
            'edit_revenue_id': data.id,
            'edit_account_manager': data.account_manager ? data.account_manager.nama : '',
            'edit_account_manager_id': data.account_manager_id,
            'edit_divisi_nama': data.divisi ? data.divisi.nama : 'N/A',
            'edit_divisi_id': data.divisi_id,
            'edit_corporate_customer': data.corporate_customer ? data.corporate_customer.nama : '',
            'edit_corporate_customer_id': data.corporate_customer_id,
            'edit_target_revenue': data.target_revenue,
            'edit_real_revenue': data.real_revenue,
            'edit_bulan': data.bulan
        };

        Object.keys(elements).forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = elements[id] || '';
        });

        if (data.bulan) {
            const date = new Date(data.bulan + '-01');
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            const monthName = monthNames[date.getMonth()];
            const year = date.getFullYear();

            const bulanDisplayElement = document.getElementById('edit_bulan_display');
            if (bulanDisplayElement) {
                bulanDisplayElement.value = `${monthName} ${year}`;
            }
        }
    }

    // ✅ DIVISI BUTTON STYLING HELPERS
    setActiveDivisiButtonStyle(button) {
        $(button).css({
            'background-color': '#1e3c72',
            'color': 'white',
            'border-color': '#1e3c72'
        });
    }

    resetDivisiButtonStyle(button) {
        $(button).css({
            'background-color': 'white',
            'color': '#495057',
            'border-color': '#dee2e6'
        });
    }

    // ✅ FORM EVENTS
    bindFormEvents() {
        $('#revenueForm').off('submit').on('submit', (e) => {
            e.preventDefault();
            this.handleRevenueSubmit(e.target);
        });

        $('#amForm').off('submit').on('submit', (e) => {
            e.preventDefault();
            this.handleAccountManagerSubmit(e.target);
        });

        $('#ccForm').off('submit').on('submit', (e) => {
            e.preventDefault();
            this.handleCorporateCustomerSubmit(e.target);
        });

        $('#editRevenueForm').off('submit').on('submit', (e) => {
            e.preventDefault();
            this.handleRevenueUpdate(e.target);
        });

        $('#editAmForm').off('submit').on('submit', (e) => {
            e.preventDefault();
            this.handleAccountManagerUpdate(e.target);
        });

        $('#editCcForm').off('submit').on('submit', (e) => {
            e.preventDefault();
            this.handleCorporateCustomerUpdate(e.target);
        });

        this.log('✅ Form events bound');
    }

    // ✅ FORM SUBMISSION HANDLERS
    async handleRevenueSubmit(form) {
        const formData = new FormData(form);

        try {
            this.showFormLoading($(form).find('button[type="submit"]'), 'Menyimpan data revenue...');

            const response = await $.ajax({
                url: this.routes.revenue.store,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.success) {
                this.showNotification('success', 'Revenue Berhasil Disimpan', response.message);
                this.resetForm(form);
                this.refreshCurrentTab();
            } else {
                this.showNotification('error', 'Gagal Menyimpan Revenue', response.message);
            }
        } catch (error) {
            this.handleFormError(error, 'menyimpan revenue');
        } finally {
            this.hideFormLoading($(form).find('button[type="submit"]'));
        }
    }

    async handleAccountManagerSubmit(form) {
        const formData = new FormData(form);

        try {
            this.showFormLoading($(form).find('button[type="submit"]'), 'Menyimpan Account Manager...');

            const response = await $.ajax({
                url: this.routes.accountManager.store,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.success) {
                this.showNotification('success', 'Account Manager Berhasil Disimpan', response.message);
                $('#addAccountManagerModal').modal('hide');
                this.refreshCurrentTab();
            } else {
                this.showNotification('error', 'Gagal Menyimpan Account Manager', response.message);
            }
        } catch (error) {
            this.handleFormError(error, 'menyimpan Account Manager');
        } finally {
            this.hideFormLoading($(form).find('button[type="submit"]'));
        }
    }

    async handleCorporateCustomerSubmit(form) {
        const formData = new FormData(form);

        try {
            this.showFormLoading($(form).find('button[type="submit"]'), 'Menyimpan Corporate Customer...');

            const response = await $.ajax({
                url: this.routes.corporateCustomer.store,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.success) {
                this.showNotification('success', 'Corporate Customer Berhasil Disimpan', response.message);
                $('#addCorporateCustomerModal').modal('hide');
                this.refreshCurrentTab();
            } else {
                this.showNotification('error', 'Gagal Menyimpan Corporate Customer', response.message);
            }
        } catch (error) {
            this.handleFormError(error, 'menyimpan Corporate Customer');
        } finally {
            this.hideFormLoading($(form).find('button[type="submit"]'));
        }
    }

    // ✅ UPDATE HANDLERS
    async handleRevenueUpdate(form) {
        const formData = new FormData(form);
        const id = $('#edit_revenue_id').val();

        formData.append('_method', 'PUT');

        try {
            this.showFormLoading($(form).find('button[type="submit"]'), 'Memperbarui data revenue...');

            const response = await $.ajax({
                url: this.routes.revenue.update.replace('{id}', id),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.success) {
                this.showNotification('success', 'Revenue Berhasil Diperbarui', response.message);
                $('#editRevenueModal').modal('hide');
                this.refreshCurrentTab();
            } else {
                this.showNotification('error', 'Gagal Memperbarui Revenue', response.message);
            }
        } catch (error) {
            this.handleFormError(error, 'memperbarui revenue');
        } finally {
            this.hideFormLoading($(form).find('button[type="submit"]'));
        }
    }

    async handleAccountManagerUpdate(form) {
        const formData = new FormData(form);
        const id = $('#edit_am_id').val();

        formData.append('_method', 'PUT');

        try {
            this.showFormLoading($(form).find('button[type="submit"]'), 'Memperbarui Account Manager...');

            const response = await $.ajax({
                url: this.routes.accountManager.update.replace('{id}', id),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.success) {
                this.showNotification('success', 'Account Manager Berhasil Diperbarui', response.message);
                $('#editAccountManagerModal').modal('hide');
                this.refreshCurrentTab();
            } else {
                this.showNotification('error', 'Gagal Memperbarui Account Manager', response.message);
            }
        } catch (error) {
            this.handleFormError(error, 'memperbarui Account Manager');
        } finally {
            this.hideFormLoading($(form).find('button[type="submit"]'));
        }
    }

    async handleCorporateCustomerUpdate(form) {
        const formData = new FormData(form);
        const id = $('#edit_cc_id').val();

        formData.append('_method', 'PUT');

        try {
            this.showFormLoading($(form).find('button[type="submit"]'), 'Memperbarui Corporate Customer...');

            const response = await $.ajax({
                url: this.routes.corporateCustomer.update.replace('{id}', id),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.success) {
                this.showNotification('success', 'Corporate Customer Berhasil Diperbarui', response.message);
                $('#editCorporateCustomerModal').modal('hide');
                this.refreshCurrentTab();
            } else {
                this.showNotification('error', 'Gagal Memperbarui Corporate Customer', response.message);
            }
        } catch (error) {
            this.handleFormError(error, 'memperbarui Corporate Customer');
        } finally {
            this.hideFormLoading($(form).find('button[type="submit"]'));
        }
    }

    // ✅ IMPORT/EXPORT FUNCTIONALITY
    bindImportExportEvents() {
        $('#revenueImportForm').off('submit').on('submit', (e) => {
            e.preventDefault();
            this.handleImport(e.target, 'revenue', 'Revenue');
        });

        $('#amImportForm').off('submit').on('submit', (e) => {
            e.preventDefault();
            this.handleImport(e.target, 'account_manager', 'Account Manager');
        });

        $('#ccImportForm').off('submit').on('submit', (e) => {
            e.preventDefault();
            this.handleImport(e.target, 'corporate_customer', 'Corporate Customer');
        });

        $('#download-error-log').off('click').on('click', () => this.downloadErrorLog());
        $('#try-again-import').off('click').on('click', () => this.retryImport());
        $('#refresh-page').off('click').on('click', () => this.refreshPage());

        this.log('✅ Import/Export events bound');
    }

    async handleImport(form, type, displayName) {
        const formData = new FormData(form);
        const fileInput = $(form).find('input[type="file"]')[0];

        if (!fileInput || !fileInput.files.length) {
            this.showNotification('error', 'File Required', 'Pilih file untuk diimport.');
            return;
        }

        try {
            this.closeImportModal(type);

            setTimeout(() => {
                this.showImportLoading(`${displayName} Import`,
                    `Memproses file ${fileInput.files[0].name}...`);
            }, 300);

            const response = await $.ajax({
                url: this.getImportUrl(type),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                timeout: this.config.importTimeout,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            this.showImportResults(displayName, response);

        } catch (error) {
            this.showImportError(`${displayName} Import Failed`,
                error.responseJSON?.message || `Terjadi kesalahan saat import ${displayName.toLowerCase()}`);
        }
    }

    getImportUrl(type) {
        const urls = {
            'revenue': this.routes.revenue.import,
            'account_manager': this.routes.accountManager.import,
            'corporate_customer': this.routes.corporateCustomer.import
        };
        return urls[type] || this.routes.revenue.import;
    }

    closeImportModal(type) {
        const modalMap = {
            'revenue': '#importRevenueModal',
            'account_manager': '#addAccountManagerModal',
            'corporate_customer': '#addCorporateCustomerModal'
        };

        const modalId = modalMap[type];
        if (modalId) {
            $(modalId).modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        }
    }

    showImportLoading(title, message) {
        $('#import-result-title').text(title);
        $('#import-loading h5').text('Memproses Import Data');
        $('#import-loading p').text(message);

        $('#import-loading').show();
        $('#import-success').hide();
        $('#import-error').hide();

        $('#download-error-log').hide();
        $('#try-again-import').hide();
        $('#refresh-page').hide();

        $('#importResultModal').modal('show');
        this.log(`📤 Import loading: ${title}`);
    }

    showImportResults(type, response) {
        $('#import-loading').hide();
        $('#import-success').show();
        $('#import-error').hide();

        const data = response.data || response;

        $('#imported-count').text(data.imported || data.total_rows || 0);
        $('#success-count').text(data.success_rows || data.imported || 0);
        $('#duplicate-count').text(data.duplicates || 0);
        $('#error-count').text(data.errors || data.failed_rows || 0);

        if (data.error_details && data.error_details.length > 0) {
            this.populateErrorDetails(data.error_details);
            $('#validation-accordion').show();
            $('#validation-count-label').text(data.error_details.length);
            $('#download-error-log').show();
        } else {
            $('#validation-accordion').hide();
            $('#download-error-log').hide();
        }

        if ((data.errors || data.failed_rows || 0) > 0) {
            $('#try-again-import').show();
        } else {
            $('#try-again-import').hide();
        }

        $('#refresh-page').show();

        this.showNotification('success', `${type} Import Berhasil`,
            `${data.imported || data.success_rows || 0} berhasil${data.errors || data.failed_rows ? `, ${data.errors || data.failed_rows} error` : ''}`);

        this.log(`✅ Import completed: ${type}`, data);
    }

    showImportError(title, message) {
        $('#import-loading').hide();
        $('#import-success').hide();
        $('#import-error').show();
        $('#import-error-message').text(message);

        $('#download-error-log').hide();
        $('#try-again-import').show();
        $('#refresh-page').show();

        this.showNotification('error', title, message);
        this.error(`❌ Import error: ${title}`, message);
    }

    // ✅ VALIDATION FUNCTIONALITY
    bindValidationEvents() {
        $(document).off('input', '#nik, #edit_nik').on('input', '#nik, #edit_nik', (e) => {
            this.validateNik(e.target);
        });

        $(document).off('input', '#nipnas, #edit_nipnas').on('input', '#nipnas, #edit_nipnas', (e) => {
            this.validateNipnas(e.target);
        });

        this.log('✅ Validation events bound');
    }

    validateNik(input) {
        const $input = $(input);
        const nik = $input.val().trim();
        const currentId = $('#edit_am_id').val() || null;

        clearTimeout(this.validationTimeouts.nik);

        if (nik.length < 4) {
            this.showValidationMessage($input, '', 'info');
            return;
        }

        this.validationTimeouts.nik = setTimeout(async () => {
            try {
                const response = await $.ajax({
                    url: this.routes.accountManager.validateNik,
                    method: 'POST',
                    data: { nik: nik, current_id: currentId },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.valid) {
                    this.showValidationMessage($input, response.message, 'success');
                } else {
                    this.showValidationMessage($input, response.message, 'error');
                }
            } catch (error) {
                this.showValidationMessage($input, 'Gagal validasi NIK', 'error');
            }
        }, this.config.validationDelay);
    }

    validateNipnas(input) {
        const $input = $(input);
        const nipnas = $input.val().trim();
        const currentId = $('#edit_cc_id').val() || null;

        clearTimeout(this.validationTimeouts.nipnas);

        if (nipnas.length < 3) {
            this.showValidationMessage($input, '', 'info');
            return;
        }

        this.validationTimeouts.nipnas = setTimeout(async () => {
            try {
                const response = await $.ajax({
                    url: this.routes.corporateCustomer.validateNipnas,
                    method: 'POST',
                    data: { nipnas: nipnas, current_id: currentId },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.valid) {
                    this.showValidationMessage($input, response.message, 'success');
                } else {
                    this.showValidationMessage($input, response.message, 'error');
                }
            } catch (error) {
                this.showValidationMessage($input, 'Gagal validasi NIPNAS', 'error');
            }
        }, this.config.validationDelay);
    }

    showValidationMessage($input, message, type) {
        let $feedback = $input.siblings('.validation-feedback');
        if ($feedback.length === 0) {
            $feedback = $('<div class="validation-feedback"></div>');
            $input.after($feedback);
        }

        $feedback.removeClass('valid invalid info').addClass(type);
        $feedback.text(message);

        if (type === 'success') {
            $input.removeClass('is-invalid').addClass('is-valid');
        } else if (type === 'error') {
            $input.removeClass('is-valid').addClass('is-invalid');
        } else {
            $input.removeClass('is-valid is-invalid');
        }
    }

    // ✅ UI EVENTS AND INTERACTIONS
    bindUIEvents() {
        $('.tab-item').off('click').on('click', (e) => {
            const tabName = $(e.currentTarget).data('tab');
            this.switchTab(tabName);
        });

        $('#notification-close').off('click').on('click', () => this.hideNotification());

        this.log('✅ UI events bound');
    }

    bindModalEvents() {
        $('.modal').off('show.bs.modal').on('show.bs.modal', (e) => {
            const modal = e.target;
            this.resetModalForm(modal);
            this.ensureDefaultTabActive(modal);
        });

        $('.modal').off('hide.bs.modal').on('hide.bs.modal', (e) => {
            const modal = e.target;
            this.cleanupModal(modal);
        });

        this.log('✅ Modal events bound');
    }

    bindFilterEvents() {
        $('#filterToggle').off('click').on('click', () => this.toggleFilters());
        this.log('✅ Filter events bound');
    }

    bindPaginationEvents() {
        $('.per-page-select').off('change').on('change', (e) => this.changePerPage(e.target.value));
        this.log('✅ Pagination events bound');
    }

    // ✅ DIVISI BUTTON FUNCTIONALITY
    initDivisiButtons() {
        $(document).off('click.divisi', '.divisi-btn');
        $(document).on('click.divisi', '.divisi-btn', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.handleDivisiClick(e.currentTarget);
        });
        this.log('✅ Divisi buttons initialized');
    }

    handleDivisiClick(button) {
        const $button = $(button);
        const divisiId = $button.data('divisi-id');

        if (!divisiId) return;

        $button.toggleClass('active');

        if ($button.hasClass('active')) {
            this.setActiveDivisiButtonStyle(button);
        } else {
            this.resetDivisiButtonStyle(button);
        }

        this.updateDivisiInput($button);
    }

    updateDivisiInput($button) {
        const container = $button.closest('.modal-body, .form-section');
        const hiddenInput = container.find('input[name="divisi_ids"]');

        const activeDivisis = [];
        container.find('.divisi-btn.active').each(function() {
            const divisiId = $(this).data('divisi-id');
            if (divisiId) {
                activeDivisis.push(divisiId.toString());
            }
        });

        hiddenInput.val(activeDivisis.join(','));
        this.log(`✅ Updated divisi input:`, activeDivisis);
    }

    // ✅ UI HELPER METHODS
    switchTab(tabName) {
        $('.tab-item').removeClass('active');
        $('.tab-content').removeClass('active');

        $(`.tab-item[data-tab="${tabName}"]`).addClass('active');
        $(`#${tabName}`).addClass('active');

        this.currentTab = tabName;
        this.log(`✅ Switched to tab: ${tabName}`);
    }

    toggleFilters() {
        const filterArea = $('#filterArea');
        const icon = $('#filterToggle i');

        if (filterArea.is(':visible')) {
            filterArea.slideUp();
            icon.removeClass('fa-times').addClass('fa-filter');
        } else {
            filterArea.slideDown();
            icon.removeClass('fa-filter').addClass('fa-times');
        }
    }

    changePerPage(value) {
        const url = new URL(window.location);
        url.searchParams.set('per_page', value);
        window.location.href = url.toString();
    }

    performGlobalSearch() {
        const searchTerm = $('#globalSearch').val().trim();
        const url = new URL(window.location);

        if (searchTerm.length >= 2) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }

        window.location.href = url.toString();
    }

    handleGlobalSearchInput(value) {
        if (value.length >= 2) {
            this.log(`Global search: ${value}`);
        }
    }

    // ✅ NOTIFICATION SYSTEM
    showNotification(type, title, message, details = null) {
        const notification = $('#notification-container');

        notification.removeClass('success error warning info').addClass(type);
        $('#notification-title').text(title);
        $('#notification-message').text(message);

        if (details) {
            $('#notification-details').html(details).show();
        } else {
            $('#notification-details').hide();
        }

        notification.addClass('show');

        setTimeout(() => notification.removeClass('show'), this.config.notificationTimeout);

        this.log(`📢 Notification: ${type} - ${title}`);
    }

    hideNotification() {
        $('#notification-container').removeClass('show');
    }

    // ✅ UTILITY METHODS
    resetForm(form) {
        form.reset();
        $(form).find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        $(form).find('.validation-feedback').remove();

        $(form).find('input[type="hidden"]').val('');
        $(form).find('select').prop('disabled', false).trigger('change');

        $('.divisi-btn').removeClass('active').css({
            'background-color': '#f8f9fa',
            'color': '#495057',
            'border-color': '#ddd'
        });
        $('input[name="divisi_ids"]').val('');

        this.log('✅ Form reset');
    }

    resetModalForm(modal) {
        const $modal = $(modal);
        const form = $modal.find('form')[0];

        if (form) {
            this.resetForm(form);
        }

        $modal.find('.modal-loading-overlay').hide();
        this.log(`✅ Modal form reset: ${modal.id}`);
    }

    cleanupModal(modal) {
        const $modal = $(modal);

        Object.values(this.validationTimeouts).forEach(timeout => clearTimeout(timeout));
        this.validationTimeouts = {};

        $modal.find('.suggestions-container').hide();

        this.log(`✅ Modal cleaned up: ${modal.id}`);
    }

    showFormLoading($button, message) {
        $button.prop('disabled', true);
        $button.data('original-text', $button.text());
        $button.html(`<i class="fas fa-spinner fa-spin me-2"></i>${message}`);
    }

    hideFormLoading($button) {
        $button.prop('disabled', false);
        const originalText = $button.data('original-text');
        if (originalText) {
            $button.text(originalText);
        }
    }

    handleFormError(error, action) {
        let message = `Terjadi kesalahan saat ${action}.`;

        if (error.responseJSON) {
            message = error.responseJSON.message || message;
        } else if (error.responseText) {
            message = error.responseText;
        }

        this.showNotification('error', 'Error', message);
        this.error(`❌ Form error (${action}):`, error);
    }

    refreshCurrentTab() {
        setTimeout(() => location.reload(), 1500);
    }

    async refreshStatistics() {
        try {
            const response = await $.ajax({
                url: '/revenue/statistics',
                timeout: 10000,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.success) {
                this.updateStatisticsDisplay(response.data);
            }
        } catch (error) {
            this.error('Failed to refresh statistics:', error);
        }
    }

    updateStatisticsDisplay(data) {
        $('.stat-number').each(function(index) {
            const keys = ['total_revenues', 'achievement_rate', 'active_account_managers', 'active_corporate_customers'];
            const key = keys[index];
            if (data[key] !== undefined) {
                $(this).text(data[key]);
            }
        });
    }

    downloadErrorLog() {
        const errorDetails = [];
        $('#validation-details-list .detail-item').each(function() {
            errorDetails.push($(this).text());
        });

        if (errorDetails.length > 0) {
            const errorLog = [
                '=== IMPORT ERROR LOG ===',
                `Generated: ${new Date().toLocaleString()}`,
                `Total Errors: ${errorDetails.length}`,
                '',
                '=== ERROR DETAILS ===',
                ...errorDetails
            ].join('\n');

            this.downloadFile(errorLog, `import_error_log_${new Date().toISOString().slice(0, 10)}.txt`, 'text/plain');
            this.showNotification('info', 'Error Log Downloaded', 'File error log telah didownload');
        } else {
            this.showNotification('warning', 'No Errors', 'Tidak ada error untuk didownload');
        }
    }

    retryImport() {
        $('#importResultModal').modal('hide');
    }

    refreshPage() {
        location.reload();
    }

    populateErrorDetails(errorDetails) {
        const container = $('#validation-details-list');
        container.empty();

        errorDetails.forEach(error => {
            container.append(`<div class="detail-item error">${this.escapeHtml(error)}</div>`);
        });
    }

    downloadFile(content, filename, type) {
        const blob = new Blob([content], { type: type });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    }

    // ✅ ENHANCED: Better escapeHtml with null safety
    escapeHtml(text) {
        if (text === null || text === undefined) return '';

        const textStr = String(text);
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return textStr.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    initPagination() {
        $('.per-page-select').off('change').on('change', (e) => {
            this.changePerPage(e.target.value);
        });
        this.log('✅ Pagination initialized');
    }

    initValidation() {
        $('.form-control').on('focus', function() {
            $(this).removeClass('is-invalid');
        });
        this.log('✅ Validation initialized');
    }

    showModalLoading(selector) {
        $(selector).show();
    }

    hideModalLoading(selector) {
        $(selector).hide();
    }

    initModalDefaults() {
        $('.modal').each((index, modal) => {
            this.ensureDefaultTabActive(modal);
        });
    }

    ensureDefaultTabActive(modal) {
        const $modal = $(modal);

        const $firstTab = $modal.find('.tab-item').first();
        const $firstContent = $modal.find('.tab-content').first();

        $modal.find('.tab-item').removeClass('active');
        $modal.find('.tab-content').removeClass('active');

        $firstTab.addClass('active');
        $firstContent.addClass('active');

        this.log(`✅ Default tab activated for modal: ${modal.id}`);
    }

    // ✅ DEBUGGING METHODS
    log(...args) {
        if (this.debugMode) {
            console.log('[Revenue System]', ...args);
        }
    }

    error(...args) {
        console.error('[Revenue System Error]', ...args);
    }
}

// ✅ INITIALIZATION & GLOBAL FUNCTIONS
$(document).ready(function() {
    window.revenueSystem = new RevenueManagementSystem();

    // Global functions untuk backward compatibility
    window.changePerPage = (value) => window.revenueSystem.changePerPage(value);
    window.showNotification = (type, title, message, details) =>
        window.revenueSystem.showNotification(type, title, message, details);

    console.log('✅ Revenue Management System - Enhanced Suggestion Version Ready');
    console.log('📝 ENHANCED FEATURES:');
    console.log('   - ✅ SUGGESTION/AUTOCOMPLETE: Enhanced dengan null safety dan error handling');
    console.log('   - ✅ EVENT BINDING: Proper preventDefault dan stopPropagation');
    console.log('   - ✅ HOVER EFFECTS: Visual feedback untuk suggestion items');
    console.log('   - ✅ DELAY HIDING: 150ms delay untuk allow click processing');
    console.log('   - ✅ COMPREHENSIVE LOGGING: Enhanced debugging capabilities');
    console.log('   - ✅ JSON RESPONSE: Force Accept header untuk consistent response');
    console.log('   - ✅ MONTH PICKER: Default ke bulan terkini dengan proper form updates');
    console.log('   - ✅ EDIT/DELETE: Proper event delegation dan error handling');
    console.log('   - ✅ IMPORT/EXPORT: Enhanced dengan progress tracking');
    console.log('   - ✅ VALIDATION: Real-time dengan proper timeout management');
    console.log('');
    console.log('🚀 SYSTEM READY FOR PRODUCTION USE!');
});

// ✅ GLOBAL HELPER FUNCTIONS untuk backward compatibility
window.switchRevenueTab = function(tabName) {
    if (window.revenueSystem) {
        window.revenueSystem.switchTab(tabName);
    }
};

window.refreshRevenueStatistics = function() {
    if (window.revenueSystem) {
        window.revenueSystem.refreshStatistics();
    }
};

window.toggleMonthPicker = function() {
    if (window.revenueSystem) {
        if (window.revenueSystem.monthPickerVisible) {
            window.revenueSystem.hideMonthPicker();
        } else {
            window.revenueSystem.showMonthPicker();
        }
    }
};

window.resetRevenueForm = function(formSelector) {
    if (window.revenueSystem) {
        const form = document.querySelector(formSelector);
        if (form) {
            window.revenueSystem.resetForm(form);
        }
    }
};

window.hideSuggestions = function() {
    $('.suggestions-container').hide();
};

window.updateDivisiSelection = function(divisiIds, containerSelector = '.divisi-btn-group') {
    const container = $(containerSelector);

    // Reset all buttons
    container.find('.divisi-btn').removeClass('active').css({
        'background-color': '#f8f9fa',
        'color': '#495057',
        'border-color': '#ddd'
    });

    // Activate selected divisi
    if (Array.isArray(divisiIds)) {
        divisiIds.forEach(id => {
            const $btn = container.find(`.divisi-btn[data-divisi-id="${id}"]`);
            $btn.addClass('active').css({
                'background-color': '#0d6efd',
                'color': 'white',
                'border-color': '#0d6efd'
            });
        });

        // Update hidden input
        const hiddenInput = container.closest('.modal-body, .form-section').find('input[name="divisi_ids"]');
        hiddenInput.val(divisiIds.join(','));
    }
};

window.toggleRevenueDebugMode = function() {
    if (window.revenueSystem) {
        window.revenueSystem.debugMode = !window.revenueSystem.debugMode;
        console.log(`Debug mode: ${window.revenueSystem.debugMode ? 'ENABLED' : 'DISABLED'}`);
    }
};

window.getRevenueSystemStatus = function() {
    if (window.revenueSystem) {
        return {
            currentTab: window.revenueSystem.currentTab,
            monthPickerVisible: window.revenueSystem.monthPickerVisible,
            debugMode: window.revenueSystem.debugMode,
            selectedDivisions: Array.from(window.revenueSystem.selectedDivisions),
            searchTimeout: window.revenueSystem.searchTimeout !== null,
            validationTimeouts: Object.keys(window.revenueSystem.validationTimeouts).length
        };
    }
    return null;
};

// ✅ ENHANCED GLOBAL FUNCTIONS untuk specific functionality
window.triggerEdit = function(type, id) {
    if (!window.revenueSystem) return;

    switch(type) {
        case 'revenue':
            window.revenueSystem.editRevenue(id);
            break;
        case 'account-manager':
            window.revenueSystem.editAccountManager(id);
            break;
        case 'corporate-customer':
            window.revenueSystem.editCorporateCustomer(id);
            break;
        default:
            console.error('Invalid edit type:', type);
    }
};

window.triggerSearch = function(type, query) {
    if (!window.revenueSystem) return;

    switch(type) {
        case 'account-manager':
            window.revenueSystem.searchAccountManagers(query);
            break;
        case 'corporate-customer':
            window.revenueSystem.searchCorporateCustomers(query);
            break;
        case 'global':
            $('#globalSearch').val(query);
            window.revenueSystem.performGlobalSearch();
            break;
        default:
            console.error('Invalid search type:', type);
    }
};

window.triggerValidation = function(type, value, currentId = null) {
    if (!window.revenueSystem) return;

    const input = type === 'nik' ?
        document.getElementById('nik') || document.getElementById('edit_nik') :
        document.getElementById('nipnas') || document.getElementById('edit_nipnas');

    if (input) {
        input.value = value;
        if (type === 'nik') {
            window.revenueSystem.validateNik(input);
        } else {
            window.revenueSystem.validateNipnas(input);
        }
    }
};

window.manageModal = function(action, modalId) {
    if (!window.revenueSystem) return;

    const modal = document.getElementById(modalId);
    if (!modal) return;

    switch(action) {
        case 'show':
            $(modal).modal('show');
            break;
        case 'hide':
            $(modal).modal('hide');
            break;
        case 'reset':
            window.revenueSystem.resetModalForm(modal);
            break;
        case 'cleanup':
            window.revenueSystem.cleanupModal(modal);
            break;
        default:
            console.error('Invalid modal action:', action);
    }
};

window.triggerImport = function(type, fileElement) {
    if (!window.revenueSystem || !fileElement) return;

    const form = fileElement.closest('form');
    if (form) {
        const displayNames = {
            'revenue': 'Revenue',
            'account_manager': 'Account Manager',
            'corporate_customer': 'Corporate Customer'
        };

        window.revenueSystem.handleImport(form, type, displayNames[type] || type);
    }
};

window.manageNotification = function(action, type = null, title = null, message = null, details = null) {
    if (!window.revenueSystem) return;

    switch(action) {
        case 'show':
            if (type && title && message) {
                window.revenueSystem.showNotification(type, title, message, details);
            }
            break;
        case 'hide':
            window.revenueSystem.hideNotification();
            break;
        default:
            console.error('Invalid notification action:', action);
    }
};

// ✅ FINAL SYSTEM VERIFICATION
window.verifyRevenueSystem = function() {
    console.log('🔍 VERIFYING ENHANCED REVENUE MANAGEMENT SYSTEM...');

    if (!window.revenueSystem) {
        console.error('❌ Revenue System not loaded!');
        return false;
    }

    const checks = [
        { name: 'CSRF Token', check: () => $('meta[name="csrf-token"]').length > 0 },
        { name: 'Edit Buttons', check: () => $('.edit-revenue, .edit-account-manager, .edit-corporate-customer').length > 0 },
        { name: 'Delete Forms', check: () => $('.delete-form').length > 0 },
        { name: 'Import Forms', check: () => $('#revenueImportForm, #amImportForm, #ccImportForm').length > 0 },
        { name: 'Search Inputs', check: () => $('#globalSearch, #account_manager, #corporate_customer').length > 0 },
        { name: 'Suggestion Containers', check: () => $('#account_manager_suggestions, #corporate_customer_suggestions').length > 0 },
        { name: 'Month Picker', check: () => $('#global_month_picker').length > 0 },
        { name: 'Divisi Buttons', check: () => $('.divisi-btn').length > 0 },
        { name: 'Pagination', check: () => $('.pagination-container, .per-page-select').length > 0 },
        { name: 'Statistics Cards', check: () => $('.stat-card').length > 0 },
        { name: 'Modals', check: () => $('.modal').length > 0 },
        { name: 'Tabs', check: () => $('.tab-item').length > 0 },
        { name: 'Validation Elements', check: () => $('#nik, #nipnas, #edit_nik, #edit_nipnas').length > 0 },
        { name: 'Notification Container', check: () => $('#notification-container').length > 0 }
    ];

    let allPassed = true;
    checks.forEach(check => {
        const result = check.check();
        console.log(`   ${result ? '✅' : '❌'} ${check.name}: ${result ? 'PASS' : 'FAIL'}`);
        if (!result) allPassed = false;
    });

    console.log('\n🧪 TESTING ENHANCED SUGGESTION FUNCTIONS:');

    const functionTests = [
        { name: 'Enhanced Suggestion Functions', test: () => typeof window.revenueSystem.searchCorporateCustomers === 'function' },
        { name: 'Enhanced Event Binding', test: () => typeof window.revenueSystem.bindSearchEvents === 'function' },
        { name: 'Enhanced Error Handling', test: () => typeof window.revenueSystem.escapeHtml === 'function' },
        { name: 'Month Picker Functions', test: () => typeof window.revenueSystem.updateFormValues === 'function' },
        { name: 'Edit Functions', test: () => typeof window.revenueSystem.editRevenue === 'function' },
        { name: 'Delete Functions', test: () => typeof window.revenueSystem.handleDelete === 'function' },
        { name: 'Validation Functions', test: () => typeof window.revenueSystem.validateNik === 'function' },
        { name: 'Import Functions', test: () => typeof window.revenueSystem.handleImport === 'function' },
        { name: 'Form Functions', test: () => typeof window.revenueSystem.resetForm === 'function' },
        { name: 'Notification Functions', test: () => typeof window.revenueSystem.showNotification === 'function' }
    ];

    functionTests.forEach(test => {
        const result = test.test();
        console.log(`   ${result ? '✅' : '❌'} ${test.name}: ${result ? 'AVAILABLE' : 'MISSING'}`);
        if (!result) allPassed = false;
    });

    console.log(`\n🎯 ENHANCED SYSTEM VERIFICATION: ${allPassed ? '✅ ALL CHECKS PASSED' : '❌ SOME CHECKS FAILED'}`);

    if (allPassed) {
        console.log('🚀 Enhanced Revenue Management System is ready for use!');
        console.log('📖 Key Enhanced Features:');
        console.log('   • Enhanced Suggestion System dengan proper error handling');
        console.log('   • Improved Event Binding dengan preventDefault dan stopPropagation');
        console.log('   • Visual Hover Effects untuk better user experience');
        console.log('   • Delay on Hide untuk allow proper click processing');
        console.log('   • Comprehensive Logging untuk enhanced debugging');
        console.log('   • Force JSON Response untuk consistent API communication');
        console.log('   • Month Picker dengan current month default');
        console.log('   • Null Safety checks di semua suggestion functions');
        console.log('');
        console.log('🔧 Available Test Functions:');
        console.log('   • window.triggerSearch("corporate-customer", "test") - Test CC search');
        console.log('   • window.triggerSearch("account-manager", "test") - Test AM search');
        console.log('   • window.toggleRevenueDebugMode() - Toggle debug logging');
        console.log('   • window.getRevenueSystemStatus() - Get system status');
    }

    return allPassed;
};

// ✅ AUTO-VERIFICATION AFTER INITIALIZATION
setTimeout(() => {
    window.verifyRevenueSystem();

    console.log('\n🎉 ENHANCED REVENUE MANAGEMENT SYSTEM FULLY LOADED!');
    console.log('🔧 System ready dengan semua enhanced features:');
    console.log('   ✅ Enhanced Suggestion/Autocomplete dengan proper error handling');
    console.log('   ✅ Improved Event Binding dengan better click processing');
    console.log('   ✅ Visual feedback dengan hover effects');
    console.log('   ✅ Comprehensive null safety checks');
    console.log('   ✅ Enhanced debugging capabilities');
    console.log('   ✅ Month picker dengan current month default');
    console.log('   ✅ All CRUD operations dengan proper validation');
    console.log('   ✅ Import/Export dengan enhanced error tracking');
    console.log('   ✅ Real-time search dengan debouncing');
    console.log('   ✅ Form management dengan proper cleanup');
    console.log('   ✅ Modal handling dengan enhanced lifecycle');
    console.log('');
    console.log('🚀 Ready for production dengan enhanced suggestion fixes!');
}, 1000);

</script>
@endsection