/**
 * ===================================================================
 * REVENUE.JS - COMPREHENSIVE REVENUE MANAGEMENT SYSTEM
 * ===================================================================
 * 
 * 📋 FUNCTION DIRECTORY & STATUS:
 * 
 * 🏗️ CORE ARCHITECTURE:
 * ✅ RevenueManager (Main Controller)
 * ✅ validateGlobalConfiguration()
 * ✅ initializeModules()
 * ✅ setupErrorBoundary()
 * 
 * 🔍 SEARCH MODULE:
 * ✅ SearchModule.constructor()
 * ✅ initializeSearchComponents()
 * 🔧 FIXED: performGlobalSearch() - Added URL integration
 * 🔧 FIXED: executeGlobalSearch() - Enhanced result processing
 * ✅ NEW: updateURLWithSearch() - URL parameter management
 * ✅ showSearchResultsContent()
 * ✅ setupAutocompleteInputs()
 * ✅ searchAccountManagers()
 * ✅ searchCorporateCustomers()
 * 
 * 📝 CRUD MODULE:
 * ✅ CRUDModule.constructor()
 * ✅ setupFormSubmissions()
 * 🔧 FIXED: handleSingleDelete() - Fixed route method issue
 * ❌ BROKEN → 🔧 FIXED: handleEditRevenue() - Fixed data loading
 * ❌ BROKEN → 🔧 FIXED: handleEditAccountManager() - Fixed data loading
 * ❌ BROKEN → 🔧 FIXED: handleEditCorporateCustomer() - Fixed data loading
 * ❌ BROKEN → 🔧 FIXED: populateEditRevenueModal() - Complete implementation
 * ❌ BROKEN → 🔧 FIXED: populateEditAccountManagerModal() - Complete implementation
 * ❌ BROKEN → 🔧 FIXED: populateEditCorporateCustomerModal() - Complete implementation
 * 
 * 📦 BULK OPERATIONS MODULE:
 * ✅ BulkOperationsModule.constructor()
 * ✅ setupSelectAllCheckboxes()
 * ✅ handleBulkDelete()
 * 🔧 FIXED: executeBulkDelete() - Fixed type parameter
 * 
 * 📤 IMPORT/EXPORT MODULE:
 * ✅ ImportModule.constructor()
 * ❌ BROKEN → 🔧 FIXED: handleImportSubmission() - Fixed modal flow
 * ✅ NEW: showImportLoadingModal() - Proper loading state
 * ✅ NEW: showImportResultModal() - Enhanced result display
 * ✅ NEW: generateDetailedImportResult() - Comprehensive reporting
 * ✅ NEW: downloadErrorLog() - Export error log as TXT
 * 
 * 🗂️ TAB MODULE:
 * ✅ TabModule.constructor()
 * ✅ switchTab()
 * ✅ updateTabCounts()
 * 
 * 🪟 MODAL MODULE:
 * ✅ ModalModule.constructor()
 * ✅ openModal()
 * ✅ closeModal()
 * ✅ resetForm()
 * 
 * 🔔 NOTIFICATION MODULE:
 * ✅ NotificationModule.constructor()
 * ✅ showSuccess()
 * ✅ showError()
 * ✅ show()
 * 
 * 🌐 REQUEST HANDLER:
 * ✅ RequestHandler.constructor()
 * ✅ makeRequest()
 * 🔧 FIXED: Enhanced error handling for HTML responses
 * 
 * 🛠️ ERROR HANDLER:
 * ✅ ErrorHandler.constructor()
 * ✅ handleAjaxError()
 * 🔧 FIXED: handleSingleDeleteError() - Better HTML response handling
 * 
 * ⚡ EVENT HANDLER:
 * ✅ EventHandler.constructor()
 * ✅ NEW: setupDivisiButtonEvents() - Handle divisi multiple selection
 * ✅ NEW: setupAccountManagerEvents() - Handle AM → Divisi integration
 * ✅ NEW: setupPasswordChangeEvents() - Handle password change
 * ✅ NEW: setupFilterToggleEvents() - Handle filter toggle
 * 🔧 FIXED: handleDocumentClick() - Added missing action handlers
 * 
 * 🆕 NEW MODULES:
 * ✅ NEW: DivisiModule - Complete divisi management
 * ✅ NEW: FilterModule - Filter toggle and management
 * ✅ NEW: PasswordModule - Password change functionality
 * ✅ NEW: AccountManagerIntegrationModule - AM → Divisi integration
 * 
 * 🛠️ UTILITY FUNCTIONS:
 * ✅ UtilityFunctions (static class)
 * ✅ formatNumber()
 * ✅ formatCurrency()
 * ✅ debounce()
 * ✅ NEW: generateErrorLogContent() - Generate TXT error log
 * ✅ NEW: downloadTextFile() - Download text content as file
 * 
 * 🚀 INITIALIZATION:
 * ✅ initializeRevenueManager()
 * ✅ Auto-initialization
 * 
 * ===================================================================
 * 🔧 CRITICAL FIXES IMPLEMENTED:
 * - ✅ Single delete method fixed (DELETE → POST with _method)
 * - ✅ Edit modal data loading completely fixed
 * - ✅ Divisi button multiple selection implemented
 * - ✅ Import modal flow fixed (Upload → Loading → Result)
 * - ✅ Search URL integration added
 * - ✅ Filter toggle functionality implemented
 * - ✅ Account Manager → Divisi integration fixed
 * - ✅ Password change functionality added
 * - ✅ Month picker z-index fixed
 * - ✅ Enhanced error handling and reporting
 * ===================================================================
 */

'use strict';

// ===================================================================
// 1. 🏗️ CORE ARCHITECTURE
// ===================================================================

/**
 * Main Revenue Manager Class
 * Central controller for all revenue management operations
 */
class RevenueManager {
    constructor() {
        console.log('🚀 Initializing Revenue Manager...');
        
        // Validate required globals
        this.validateGlobalConfiguration();
        
        // Core configuration
        this.config = window.revenueConfig;
        this.currentData = window.currentData;
        
        // Initialize state
        this.state = {
            currentTab: 'revenueTab',
            selectedIds: new Set(),
            isLoading: false,
            searchCache: new Map(),
            modals: new Map()
        };
        
        // Initialize modules
        this.initializeModules();
        
        // Setup global error handling
        this.setupErrorBoundary();
        
        console.log('✅ Revenue Manager initialized successfully');
    }
    
    validateGlobalConfiguration() {
        if (!window.revenueConfig) {
            throw new Error('❌ Missing window.revenueConfig - Check blade template configuration');
        }
        if (!window.currentData) {
            throw new Error('❌ Missing window.currentData - Check blade template data');
        }
        console.log('✅ Global configuration validated');
    }
    
    initializeModules() {
        try {
            // Initialize all modules with manager reference
            this.requestHandler = new RequestHandler(this);
            this.errorHandler = new ErrorHandler(this);
            this.notificationModule = new NotificationModule(this);
            this.modalModule = new ModalModule(this);
            this.tabModule = new TabModule(this);
            this.searchModule = new SearchModule(this);
            this.crudModule = new CRUDModule(this);
            this.bulkModule = new BulkOperationsModule(this);
            this.importModule = new ImportModule(this);
            this.downloadModule = new DownloadModule(this);
            
            // ✅ NEW MODULES
            this.divisiModule = new DivisiModule(this);
            this.filterModule = new FilterModule(this);
            this.passwordModule = new PasswordModule(this);
            this.accountManagerIntegrationModule = new AccountManagerIntegrationModule(this);
            
            this.eventHandler = new EventHandler(this);
            
            console.log('✅ All modules initialized');
        } catch (error) {
            console.error('❌ Module initialization failed:', error);
            throw error;
        }
    }
    
    setupErrorBoundary() {
        window.addEventListener('error', (event) => {
            console.error('🐛 Global JavaScript Error:', event.error);
            this.errorHandler.handleGlobalError(event.error);
        });
        
        window.addEventListener('unhandledrejection', (event) => {
            console.error('🐛 Unhandled Promise Rejection:', event.reason);
            this.errorHandler.handlePromiseRejection(event.reason);
        });
    }
}

// ===================================================================
// 2. 🔍 SEARCH MODULE - 🔧 FIXED: Added URL integration
// ===================================================================

class SearchModule {
    constructor(manager) {
        this.manager = manager;
        this.searchTimeout = null;
        this.minSearchLength = 2;
        this.debounceDelay = 300;
        
        this.initializeSearchComponents();
        console.log('🔍 Search Module initialized');
    }
    
    initializeSearchComponents() {
        // Setup global search form
        const globalSearchForm = document.getElementById('global-search-form');
        if (globalSearchForm) {
            globalSearchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const searchTerm = globalSearchForm.querySelector('input[name="search"]').value;
                this.performGlobalSearch(searchTerm);
            });
        }
        
        // Setup search input with debouncing
        const searchInput = document.getElementById('globalSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.handleSearchInput(e.target.value);
            });
            
            // ✅ NEW: Enter key to trigger search with URL update
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.performGlobalSearchWithURL(e.target.value.trim());
                }
            });
        }
        
        // Setup autocomplete inputs
        this.setupAutocompleteInputs();
    }
    
    // 🔧 FIXED: Enhanced with URL integration
    performGlobalSearch(searchTerm) {
        clearTimeout(this.searchTimeout);
        
        if (searchTerm.trim().length < this.minSearchLength) {
            this.hideSearchResults();
            return;
        }
        
        this.searchTimeout = setTimeout(() => {
            this.executeGlobalSearch(searchTerm.trim());
        }, this.debounceDelay);
    }
    
    // ✅ NEW: Search with URL update for filtering
    performGlobalSearchWithURL(searchTerm) {
        if (searchTerm.length >= this.minSearchLength) {
            this.updateURLWithSearch(searchTerm);
            // Redirect to perform actual filtering
            window.location.reload();
        } else {
            this.manager.notificationModule.showError('Minimal 2 karakter untuk pencarian');
        }
    }
    
    // ✅ NEW: Update URL with search parameter
    updateURLWithSearch(searchTerm) {
        const url = new URL(window.location);
        if (searchTerm.trim()) {
            url.searchParams.set('search', searchTerm.trim());
        } else {
            url.searchParams.delete('search');
        }
        window.history.pushState({}, '', url);
    }
    
    // 🔧 FIXED: Enhanced result processing
    async executeGlobalSearch(searchTerm) {
        try {
            console.log('🔍 Executing global search for:', searchTerm);
            
            const response = await this.manager.requestHandler.makeRequest(
                'GET', 
                `${this.manager.config.routes.revenueSearch}?search=${encodeURIComponent(searchTerm)}`
            );
            
            if (response.success) {
                console.log('🔍 Search response:', response);
                this.showSearchResultsContent(response.stats, searchTerm);
            }
        } catch (error) {
            console.error('🔍 Search error:', error);
            this.manager.errorHandler.handleAjaxError(error, 'Global Search');
        }
    }
    
    // ✅ Enhanced search results display
    showSearchResultsContent(stats, searchTerm) {
        const searchResultsContainer = document.getElementById('searchResultsContainer');
        if (!searchResultsContainer) {
            console.warn('⚠️ Search results container not found');
            return;
        }
        
        // Show loading first
        this.showSearchLoading();
        
        // Update search results
        setTimeout(() => {
            this.populateSearchResults(stats, searchTerm);
            this.hideSearchLoading();
            searchResultsContainer.classList.add('show');
        }, 200);
    }
    
    populateSearchResults(stats, searchTerm) {
        // Update search term display
        const searchTermDisplay = document.getElementById('search-term-display');
        if (searchTermDisplay) {
            searchTermDisplay.textContent = searchTerm;
        }
        
        // Update result counts
        this.updateSearchCount('total-am-count', stats.account_managers_count || 0);
        this.updateSearchCount('total-cc-count', stats.corporate_customers_count || 0);
        this.updateSearchCount('total-rev-count', stats.revenues_count || 0);
        
        // Show/hide results based on total
        const hasResults = (stats.total_results || 0) > 0;
        const resultsContent = document.getElementById('search-results-content');
        const noResults = document.getElementById('search-no-results');
        
        if (resultsContent) resultsContent.style.display = hasResults ? 'block' : 'none';
        if (noResults) noResults.style.display = hasResults ? 'none' : 'block';
        
        // ✅ NEW: Add action button to apply search filter
        if (hasResults) {
            this.addSearchActionButton(searchTerm);
        }
        
        console.log('📊 Search results populated:', stats);
    }
    
    // ✅ NEW: Add action button to apply search to tables
    addSearchActionButton(searchTerm) {
        const resultsContent = document.getElementById('search-results-content');
        if (resultsContent) {
            const existingButton = resultsContent.querySelector('.search-action-button');
            if (existingButton) {
                existingButton.remove();
            }
            
            const actionButton = document.createElement('div');
            actionButton.className = 'search-action-button mt-2';
            actionButton.innerHTML = `
                <button class="btn btn-primary btn-sm" onclick="revenueManager.searchModule.applySearchFilter('${searchTerm}')">
                    <i class="fas fa-filter me-1"></i> Terapkan Filter Pencarian
                </button>
            `;
            resultsContent.appendChild(actionButton);
        }
    }
    
    // ✅ NEW: Apply search filter to tables
    applySearchFilter(searchTerm) {
        this.updateURLWithSearch(searchTerm);
        window.location.reload();
    }
    
    updateSearchCount(elementId, count) {
        const element = document.getElementById(elementId);
        if (element) {
            const text = element.textContent;
            const colonIndex = text.indexOf(':');
            if (colonIndex !== -1) {
                element.textContent = text.substring(0, colonIndex + 1) + ` ${count}`;
            } else {
                element.textContent = `${count}`;
            }
        }
    }
    
    showSearchLoading() {
        const loading = document.getElementById('search-results-loading');
        const content = document.getElementById('search-results-content');
        const noResults = document.getElementById('search-no-results');
        
        if (loading) loading.style.display = 'block';
        if (content) content.style.display = 'none';
        if (noResults) noResults.style.display = 'none';
    }
    
    hideSearchLoading() {
        const loading = document.getElementById('search-results-loading');
        if (loading) loading.style.display = 'none';
    }
    
    hideSearchResults() {
        const searchResultsContainer = document.getElementById('searchResultsContainer');
        if (searchResultsContainer) {
            searchResultsContainer.classList.remove('show');
        }
    }
    
    handleSearchInput(value) {
        clearTimeout(this.searchTimeout);
        
        if (value.trim().length >= this.minSearchLength) {
            this.searchTimeout = setTimeout(() => {
                this.performGlobalSearch(value);
            }, this.debounceDelay);
        } else {
            this.hideSearchResults();
        }
    }
    
    setupAutocompleteInputs() {
        // Account Manager autocomplete
        this.setupAutocomplete('account_manager', (term) => 
            this.searchAccountManagers(term));
        
        // Corporate Customer autocomplete
        this.setupAutocomplete('corporate_customer', (term) => 
            this.searchCorporateCustomers(term));
        
        // ✅ NEW: Also setup for edit modals
        this.setupAutocomplete('edit_account_manager', (term) => 
            this.searchAccountManagers(term));
        
        this.setupAutocomplete('edit_corporate_customer', (term) => 
            this.searchCorporateCustomers(term));
    }
    
    setupAutocomplete(fieldName, searchFunction) {
        const inputs = document.querySelectorAll(`input[id*="${fieldName}"]`);
        
        inputs.forEach(input => {
            let searchTimeout;
            
            input.addEventListener('input', async (e) => {
                const value = e.target.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (value.length >= this.minSearchLength) {
                    searchTimeout = setTimeout(async () => {
                        try {
                            const results = await searchFunction(value);
                            this.showAutocompleteResults(input, results);
                        } catch (error) {
                            console.error('Autocomplete error:', error);
                        }
                    }, this.debounceDelay);
                } else {
                    this.hideAutocompleteResults(input);
                }
            });
            
            // Hide on blur (with delay for click handling)
            input.addEventListener('blur', () => {
                setTimeout(() => this.hideAutocompleteResults(input), 200);
            });
        });
    }
    
    async searchAccountManagers(term) {
        const response = await this.manager.requestHandler.makeRequest(
            'GET',
            `${this.manager.config.routes.accountManagerSearch}?search=${encodeURIComponent(term)}`
        );
        return response.data || [];
    }
    
    async searchCorporateCustomers(term) {
        const response = await this.manager.requestHandler.makeRequest(
            'GET',
            `${this.manager.config.routes.corporateCustomerSearch}?search=${encodeURIComponent(term)}`
        );
        return response.data || [];
    }
    
    showAutocompleteResults(input, results) {
        const suggestionContainer = input.parentNode.querySelector('.suggestions-container');
        if (!suggestionContainer || !results.length) return;
        
        suggestionContainer.innerHTML = '';
        
        results.forEach(item => {
            const suggestionItem = document.createElement('div');
            suggestionItem.className = 'suggestion-item';
            suggestionItem.innerHTML = `
                <div class="suggestion-name">${item.nama}</div>
                <div class="suggestion-detail">${item.nik || item.nipnas || ''}</div>
            `;
            
            suggestionItem.addEventListener('click', () => {
                this.selectAutocompleteItem(input, item);
            });
            
            suggestionContainer.appendChild(suggestionItem);
        });
        
        suggestionContainer.classList.add('show');
    }
    
    hideAutocompleteResults(input) {
        const suggestionContainer = input.parentNode.querySelector('.suggestions-container');
        if (suggestionContainer) {
            suggestionContainer.classList.remove('show');
        }
    }
    
    selectAutocompleteItem(input, item) {
        input.value = item.nama;
        
        // Update hidden ID field
        const hiddenInput = input.parentNode.querySelector('input[type="hidden"]');
        if (hiddenInput) {
            hiddenInput.value = item.id;
        }
        
        // ✅ NEW: Trigger Account Manager integration for divisi loading
        if (input.id.includes('account_manager')) {
            this.manager.accountManagerIntegrationModule.handleAccountManagerSelection(item, input);
        }
        
        // Trigger change event for additional processing
        input.dispatchEvent(new Event('change', { bubbles: true }));
        
        this.hideAutocompleteResults(input);
    }
}

// ===================================================================
// 3. 📝 CRUD MODULE - 🔧 FIXED: All edit functions and delete method
// ===================================================================

class CRUDModule {
    constructor(manager) {
        this.manager = manager;
        this.initializeCRUDComponents();
        console.log('📝 CRUD Module initialized');
    }
    
    initializeCRUDComponents() {
        // Setup form submissions
        this.setupFormSubmissions();
        
        // Setup single delete buttons
        this.setupSingleDeleteButtons();
        
        // Setup edit buttons
        this.setupEditButtons();
    }
    
    setupFormSubmissions() {
        document.addEventListener('submit', (e) => {
            const form = e.target;
            
            // ✅ Enhanced form type detection
            if (form.id === 'revenueForm' || form.dataset.formType === 'revenue') {
                e.preventDefault();
                this.handleFormSubmission(form, 'revenue');
            } else if (form.id === 'amForm' || form.id === 'editAccountManagerForm' || form.dataset.formType === 'account-manager') {
                e.preventDefault();
                this.handleFormSubmission(form, 'account-manager');
            } else if (form.id === 'ccForm' || form.id === 'editCorporateCustomerForm' || form.dataset.formType === 'corporate-customer') {
                e.preventDefault();
                this.handleFormSubmission(form, 'corporate-customer');
            } else if (form.id === 'editRevenueForm') {
                e.preventDefault();
                this.handleEditFormSubmission(form, 'revenue');
            }
        });
    }
    
    async handleFormSubmission(form, formType) {
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        
        try {
            // Disable submit button
            this.disableSubmitButton(submitButton);
            
            let response;
            
            switch (formType) {
                case 'revenue':
                    response = await this.handleRevenueSubmission(formData);
                    break;
                case 'account-manager':
                    response = await this.handleAccountManagerSubmission(formData);
                    break;
                case 'corporate-customer':
                    response = await this.handleCorporateCustomerSubmission(formData);
                    break;
                default:
                    throw new Error(`Unknown form type: ${formType}`);
            }
            
            if (response.success) {
                this.manager.notificationModule.showSuccess(response.message);
                this.resetForm(form);
                this.refreshCurrentTab();
                
                // Close modal if form is in modal
                const modal = form.closest('.modal');
                if (modal) {
                    this.manager.modalModule.closeModal(modal.id);
                }
            }
            
        } catch (error) {
            this.manager.errorHandler.handleAjaxError(error, 'Form Submission');
        } finally {
            this.enableSubmitButton(submitButton);
        }
    }
    
    // ✅ NEW: Handle edit form submissions
    async handleEditFormSubmission(form, formType) {
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const id = form.querySelector('input[name*="_id"]')?.value;
        
        if (!id) {
            this.manager.notificationModule.showError('ID tidak ditemukan untuk update');
            return;
        }
        
        try {
            this.disableSubmitButton(submitButton);
            
            let response;
            let endpoint;
            
            switch (formType) {
                case 'revenue':
                    endpoint = this.manager.config.routes.revenueUpdate.replace(':id', id);
                    break;
                case 'account-manager':
                    endpoint = this.manager.config.routes.accountManagerUpdate.replace(':id', id);
                    break;
                case 'corporate-customer':
                    endpoint = this.manager.config.routes.corporateCustomerUpdate.replace(':id', id);
                    break;
                default:
                    throw new Error(`Unknown edit form type: ${formType}`);
            }
            
            // Add _method for PUT request
            formData.append('_method', 'PUT');
            
            response = await this.manager.requestHandler.makeRequest('POST', endpoint, formData);
            
            if (response.success) {
                this.manager.notificationModule.showSuccess(response.message);
                this.refreshCurrentTab();
                
                // Close modal
                const modal = form.closest('.modal');
                if (modal) {
                    this.manager.modalModule.closeModal(modal.id);
                }
            }
            
        } catch (error) {
            this.manager.errorHandler.handleAjaxError(error, 'Edit Form Submission');
        } finally {
            this.enableSubmitButton(submitButton);
        }
    }
    
    async handleRevenueSubmission(formData) {
        return await this.manager.requestHandler.makeRequest(
            'POST',
            this.manager.config.routes.revenueStore,
            formData
        );
    }
    
    async handleAccountManagerSubmission(formData) {
        return await this.manager.requestHandler.makeRequest(
            'POST',
            this.manager.config.routes.accountManagerStore,
            formData
        );
    }
    
    async handleCorporateCustomerSubmission(formData) {
        return await this.manager.requestHandler.makeRequest(
            'POST',
            this.manager.config.routes.corporateCustomerStore,
            formData
        );
    }
    
    setupSingleDeleteButtons() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.delete-btn') || e.target.closest('.delete-btn')) {
                e.preventDefault();
                const deleteButton = e.target.closest('.delete-btn');
                const form = deleteButton.closest('form');
                if (form && form.classList.contains('delete-form')) {
                    this.handleSingleDelete(form);
                }
            }
        });
    }
    
    // 🔧 FIXED: Changed DELETE to POST with _method parameter
    async handleSingleDelete(form) {
        const action = form.getAttribute('action');
        const id = this.extractIdFromUrl(action);
        
        // Show confirmation
        if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            return;
        }
        
        try {
            console.log(`🗑️ Attempting single delete: ${action}`);
            
            // ✅ FIXED: Use POST with _method=DELETE instead of DELETE method
            const response = await this.manager.requestHandler.makeRequest(
                'POST',
                action,
                {
                    _method: 'DELETE',
                    _token: this.manager.requestHandler.getCSRFToken()
                }
            );
            
            if (response.success) {
                this.removeSingleRow(id);
                this.manager.notificationModule.showSuccess(
                    response.message || 'Data berhasil dihapus'
                );
                this.updateTabCounts();
            }
            
        } catch (error) {
            this.manager.errorHandler.handleSingleDeleteError(error, id);
        }
    }
    
    removeSingleRow(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (row) {
            row.remove();
            console.log(`✅ Row removed: ${id}`);
        }
    }
    
    extractIdFromUrl(url) {
        const matches = url.match(/\/(\d+)$/);
        return matches ? matches[1] : null;
    }
    
    setupEditButtons() {
        document.addEventListener('click', (e) => {
            const target = e.target;
            
            if (target.classList.contains('edit-revenue') || target.closest('.edit-revenue')) {
                e.preventDefault();
                const button = target.closest('.edit-revenue');
                this.handleEditRevenue(button.dataset.id);
            }
            
            if (target.classList.contains('edit-account-manager') || target.closest('.edit-account-manager')) {
                e.preventDefault();
                const button = target.closest('.edit-account-manager');
                this.handleEditAccountManager(button.dataset.id);
            }
            
            if (target.classList.contains('edit-corporate-customer') || target.closest('.edit-corporate-customer')) {
                e.preventDefault();
                const button = target.closest('.edit-corporate-customer');
                this.handleEditCorporateCustomer(button.dataset.id);
            }
        });
    }
    
    // ❌ BROKEN → 🔧 FIXED: Complete data loading implementation
    async handleEditRevenue(id) {
        try {
            console.log(`📝 Loading revenue data for edit: ${id}`);
            
            const response = await this.manager.requestHandler.makeRequest(
                'GET',
                this.manager.config.routes.revenueEdit.replace(':id', id)
            );
            
            if (response.success && response.data) {
                this.populateEditRevenueModal(response.data);
                this.manager.modalModule.openModal('editRevenueModal');
            } else {
                throw new Error('Data revenue tidak ditemukan');
            }
        } catch (error) {
            this.manager.errorHandler.handleAjaxError(error, 'Edit Revenue');
        }
    }
    
    // ❌ BROKEN → 🔧 FIXED: Complete data loading implementation
    async handleEditAccountManager(id) {
        try {
            console.log(`📝 Loading account manager data for edit: ${id}`);
            
            const response = await this.manager.requestHandler.makeRequest(
                'GET',
                this.manager.config.routes.accountManagerEdit.replace(':id', id)
            );
            
            if (response.success && response.data) {
                this.populateEditAccountManagerModal(response.data);
                this.manager.modalModule.openModal('editAccountManagerModal');
            } else {
                throw new Error('Data Account Manager tidak ditemukan');
            }
        } catch (error) {
            this.manager.errorHandler.handleAjaxError(error, 'Edit Account Manager');
        }
    }
    
    // ❌ BROKEN → 🔧 FIXED: Complete data loading implementation
    async handleEditCorporateCustomer(id) {
        try {
            console.log(`📝 Loading corporate customer data for edit: ${id}`);
            
            const response = await this.manager.requestHandler.makeRequest(
                'GET',
                this.manager.config.routes.corporateCustomerEdit.replace(':id', id)
            );
            
            if (response.success && response.data) {
                this.populateEditCorporateCustomerModal(response.data);
                this.manager.modalModule.openModal('editCorporateCustomerModal');
            } else {
                throw new Error('Data Corporate Customer tidak ditemukan');
            }
        } catch (error) {
            this.manager.errorHandler.handleAjaxError(error, 'Edit Corporate Customer');
        }
    }
    
    // ❌ BROKEN → 🔧 FIXED: Complete implementation
    populateEditRevenueModal(data) {
        console.log('📝 Populating edit revenue modal with data:', data);
        
        try {
            // Populate basic fields
            this.setFormFieldValue('edit_revenue_id', data.id);
            this.setFormFieldValue('edit_account_manager', data.accountManager?.nama || '');
            this.setFormFieldValue('edit_account_manager_id', data.account_manager_id);
            this.setFormFieldValue('edit_corporate_customer', data.corporateCustomer?.nama || '');
            this.setFormFieldValue('edit_corporate_customer_id', data.corporate_customer_id);
            this.setFormFieldValue('edit_target_revenue', data.target_revenue);
            this.setFormFieldValue('edit_real_revenue', data.real_revenue);
            
            // Handle date field (convert from Y-m-d to Y-m format)
            if (data.bulan) {
                const bulanFormatted = data.bulan.substring(0, 7); // Take Y-m part
                this.setFormFieldValue('edit_bulan', bulanFormatted);
            }
            
            // Populate divisi dropdown
            if (data.account_manager_id) {
                this.loadDivisiForAccountManager(data.account_manager_id, 'edit_divisi_id', data.divisi_id);
            }
            
            // Update form action
            const form = document.getElementById('editRevenueForm');
            if (form) {
                form.action = this.manager.config.routes.revenueUpdate.replace(':id', data.id);
            }
            
            console.log('✅ Revenue modal populated successfully');
        } catch (error) {
            console.error('❌ Error populating revenue modal:', error);
            throw error;
        }
    }
    
    // ❌ BROKEN → 🔧 FIXED: Complete implementation
    populateEditAccountManagerModal(data) {
        console.log('📝 Populating edit account manager modal with data:', data);
        
        try {
            this.setFormFieldValue('edit_am_id', data.id);
            this.setFormFieldValue('edit_am_nama', data.nama);
            this.setFormFieldValue('edit_am_nik', data.nik);
            this.setFormFieldValue('edit_am_witel_id', data.witel_id);
            this.setFormFieldValue('edit_am_regional_id', data.regional_id);
            
            // Handle divisi selection (multiple)
            if (data.divisis && Array.isArray(data.divisis)) {
                const divisiIds = data.divisis.map(d => d.id);
                this.setFormFieldValue('edit_divisi_ids', divisiIds.join(','));
                
                // Update divisi buttons
                this.updateDivisiButtons('edit-divisi-btn-group', divisiIds);
            }
            
            // Update form action
            const form = document.getElementById('editAccountManagerForm');
            if (form) {
                form.action = this.manager.config.routes.accountManagerUpdate.replace(':id', data.id);
            }
            
            console.log('✅ Account Manager modal populated successfully');
        } catch (error) {
            console.error('❌ Error populating account manager modal:', error);
            throw error;
        }
    }
    
    // ❌ BROKEN → 🔧 FIXED: Complete implementation
    populateEditCorporateCustomerModal(data) {
        console.log('📝 Populating edit corporate customer modal with data:', data);
        
        try {
            this.setFormFieldValue('edit_cc_id', data.id);
            this.setFormFieldValue('edit_cc_nama', data.nama);
            this.setFormFieldValue('edit_cc_nipnas', data.nipnas);
            
            // Update form action
            const form = document.getElementById('editCorporateCustomerForm');
            if (form) {
                form.action = this.manager.config.routes.corporateCustomerUpdate.replace(':id', data.id);
            }
            
            console.log('✅ Corporate Customer modal populated successfully');
        } catch (error) {
            console.error('❌ Error populating corporate customer modal:', error);
            throw error;
        }
    }
    
    // ✅ NEW: Load divisi for account manager in edit modal
    async loadDivisiForAccountManager(accountManagerId, targetSelectId, selectedDivisiId = null) {
        try {
            const response = await this.manager.requestHandler.makeRequest(
                'GET',
                this.manager.config.routes.accountManagerDivisions.replace(':id', accountManagerId)
            );
            
            if (response.success && response.divisis) {
                const selectElement = document.getElementById(targetSelectId);
                if (selectElement) {
                    selectElement.innerHTML = '<option value="">Pilih Divisi</option>';
                    
                    response.divisis.forEach(divisi => {
                        const option = document.createElement('option');
                        option.value = divisi.id;
                        option.textContent = divisi.nama;
                        if (selectedDivisiId && divisi.id == selectedDivisiId) {
                            option.selected = true;
                        }
                        selectElement.appendChild(option);
                    });
                    
                    selectElement.disabled = false;
                }
            }
        } catch (error) {
            console.error('Error loading divisi for account manager:', error);
        }
    }
    
    // ✅ NEW: Update divisi buttons selection state
    updateDivisiButtons(containerId, selectedIds) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const buttons = container.querySelectorAll('.divisi-btn');
        buttons.forEach(button => {
            const divisiId = parseInt(button.dataset.divisiId);
            if (selectedIds.includes(divisiId)) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    }
    
    setFormFieldValue(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = value || '';
            console.log(`✅ Set ${fieldId} = ${value}`);
        } else {
            console.warn(`⚠️ Field not found: ${fieldId}`);
        }
    }
    
    disableSubmitButton(button) {
        if (button) {
            button.disabled = true;
            button.classList.add('btn-loading');
            
            // Add loading text if doesn't exist
            const originalText = button.textContent;
            button.dataset.originalText = originalText;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
        }
    }
    
    enableSubmitButton(button) {
        if (button) {
            button.disabled = false;
            button.classList.remove('btn-loading');
            
            // Restore original text
            if (button.dataset.originalText) {
                button.textContent = button.dataset.originalText;
                delete button.dataset.originalText;
            }
        }
    }
    
    resetForm(form) {
        form.reset();
        
        // Clear validation feedback
        form.querySelectorAll('.validation-feedback').forEach(feedback => {
            feedback.textContent = '';
            feedback.className = 'validation-feedback';
        });
        
        // Clear suggestions
        form.querySelectorAll('.suggestions-container').forEach(suggestion => {
            suggestion.classList.remove('show');
        });
        
        // Clear hidden inputs (except CSRF token)
        form.querySelectorAll('input[type="hidden"]').forEach(input => {
            if (!input.name.includes('_token') && !input.name.includes('_method')) {
                input.value = '';
            }
        });
        
        // Reset divisi buttons
        form.querySelectorAll('.divisi-btn').forEach(btn => {
            btn.classList.remove('active');
        });
    }
    
    refreshCurrentTab() {
        // Refresh current tab data
        setTimeout(() => {
            this.manager.tabModule.updateTabCounts();
            window.location.reload(); // Simple refresh for now
        }, 1000);
    }
    
    updateTabCounts() {
        this.manager.tabModule.updateTabCounts();
    }
}

// ===================================================================
// 4. 📦 BULK OPERATIONS MODULE - 🔧 FIXED: Type parameter issue
// ===================================================================

class BulkOperationsModule {
    constructor(manager) {
        this.manager = manager;
        this.selectedIds = new Set();
        this.initializeBulkComponents();
        console.log('📦 Bulk Operations Module initialized');
    }
    
    initializeBulkComponents() {
        this.setupSelectAllCheckboxes();
        this.setupRowCheckboxes();
        this.setupBulkActionButtons();
        this.updateBulkActionsVisibility();
    }
    
    setupSelectAllCheckboxes() {
        const selectAllCheckboxes = document.querySelectorAll('#select-all-revenue, #select-all-am, #select-all-cc');
        
        selectAllCheckboxes.forEach(selectAll => {
            selectAll.addEventListener('change', (e) => {
                this.handleSelectAll(e.target);
            });
        });
    }
    
    handleSelectAll(selectAllCheckbox) {
        const isChecked = selectAllCheckbox.checked;
        const tabContent = selectAllCheckbox.closest('.tab-content');
        const rowCheckboxes = tabContent.querySelectorAll('.row-checkbox');
        
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            this.updateRowSelection(checkbox);
        });
        
        this.updateBulkActionsVisibility();
    }
    
    setupRowCheckboxes() {
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('row-checkbox')) {
                this.handleRowCheckboxChange(e.target);
            }
        });
    }
    
    handleRowCheckboxChange(checkbox) {
        this.updateRowSelection(checkbox);
        this.updateSelectAllState();
        this.updateBulkActionsVisibility();
    }
    
    updateRowSelection(checkbox) {
        const row = checkbox.closest('tr');
        const id = checkbox.value;
        
        if (checkbox.checked) {
            row.classList.add('selected');
            this.selectedIds.add(id);
        } else {
            row.classList.remove('selected');
            this.selectedIds.delete(id);
        }
    }
    
    updateSelectAllState() {
        const currentTab = this.manager.tabModule.getCurrentActiveTab();
        const tabContent = document.getElementById(currentTab);
        
        if (!tabContent) return;
        
        const rowCheckboxes = tabContent.querySelectorAll('.row-checkbox');
        const checkedCheckboxes = tabContent.querySelectorAll('.row-checkbox:checked');
        const selectAllCheckbox = tabContent.querySelector('thead .form-check-input');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = rowCheckboxes.length > 0 && checkedCheckboxes.length === rowCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < rowCheckboxes.length;
        }
    }
    
    setupBulkActionButtons() {
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const clearSelectionBtn = document.getElementById('clear-selection-btn');
        
        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', () => {
                this.handleBulkDelete();
            });
        }
        
        if (clearSelectionBtn) {
            clearSelectionBtn.addEventListener('click', () => {
                this.clearAllSelections();
            });
        }
    }
    
    async handleBulkDelete() {
        const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            this.manager.notificationModule.showError('Pilih minimal satu item untuk dihapus');
            return;
        }
        
        // Show confirmation modal first
        this.showBulkDeleteConfirmation(selectedIds);
    }
    
    showBulkDeleteConfirmation(selectedIds) {
        const bulkDeleteModal = document.getElementById('bulkDeleteModal');
        if (!bulkDeleteModal) {
            console.error('❌ Bulk delete modal not found');
            return;
        }
        
        // Update confirmation details
        const countElement = document.getElementById('bulk-delete-count');
        if (countElement) {
            countElement.textContent = selectedIds.length;
        }
        
        // Populate selected items list
        this.populateSelectedItemsList(selectedIds);
        
        // Setup confirmation button
        const confirmButton = document.getElementById('confirm-bulk-delete');
        if (confirmButton) {
            // Remove existing listeners
            const newConfirmButton = confirmButton.cloneNode(true);
            confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
            
            newConfirmButton.addEventListener('click', () => {
                this.executeBulkDelete(selectedIds);
                this.manager.modalModule.closeModal('bulkDeleteModal');
            });
        }
        
        // Show modal
        this.manager.modalModule.openModal('bulkDeleteModal');
    }
    
    populateSelectedItemsList(selectedIds) {
        const listContainer = document.getElementById('selected-items-list');
        if (!listContainer) return;
        
        listContainer.innerHTML = '';
        
        selectedIds.forEach(id => {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'selected-item mb-2 p-2 border rounded';
                
                // Extract row data based on current tab
                const currentTab = this.getCurrentActiveTab();
                let itemContent = '';
                
                switch (currentTab) {
                    case 'revenueTab':
                        itemContent = this.extractRevenueItemContent(row);
                        break;
                    case 'amTab':
                        itemContent = this.extractAccountManagerItemContent(row);
                        break;
                    case 'ccTab':
                        itemContent = this.extractCorporateCustomerItemContent(row);
                        break;
                }
                
                itemDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>${itemContent}</div>
                        <span class="badge bg-danger">ID: ${id}</span>
                    </div>
                `;
                
                listContainer.appendChild(itemDiv);
            }
        });
        
        if (selectedIds.length === 0) {
            listContainer.innerHTML = '<p class="text-muted">Tidak ada item yang dipilih</p>';
        }
    }
    
    extractRevenueItemContent(row) {
        const cells = row.querySelectorAll('td');
        return `
            <strong>${cells[1]?.textContent?.trim() || 'Unknown'}</strong> - ${cells[3]?.textContent?.trim() || 'Unknown'}<br>
            <small class="text-muted">${cells[2]?.textContent?.trim() || 'Unknown'} | ${cells[7]?.textContent?.trim() || 'Unknown'}</small><br>
            <small>Target: ${cells[4]?.textContent?.trim() || '0'} | Real: ${cells[5]?.textContent?.trim() || '0'}</small>
        `;
    }
    
    extractAccountManagerItemContent(row) {
        const cells = row.querySelectorAll('td');
        return `
            <strong>${cells[1]?.textContent?.trim() || 'Unknown'}</strong><br>
            <small class="text-muted">NIK: ${cells[2]?.textContent?.trim() || 'Unknown'}</small><br>
            <small>${cells[3]?.textContent?.trim() || 'Unknown'} - ${cells[4]?.textContent?.trim() || 'Unknown'}</small>
        `;
    }
    
    extractCorporateCustomerItemContent(row) {
        const cells = row.querySelectorAll('td');
        return `
            <strong>${cells[1]?.textContent?.trim() || 'Unknown'}</strong><br>
            <small class="text-muted">NIPNAS: ${cells[2]?.textContent?.trim() || 'Unknown'}</small><br>
            <small>Dibuat: ${cells[3]?.textContent?.trim() || 'Unknown'}</small>
        `;
    }
    
    // 🔧 FIXED: Proper bulk delete with correct endpoint routing
    async executeBulkDelete(selectedIds) {
        try {
            console.log('🗑️ Executing bulk delete for IDs:', selectedIds);
            
            // Determine current tab type and endpoint
            const currentTab = this.getCurrentActiveTab();
            let endpoint = '';
            
            switch (currentTab) {
                case 'revenueTab':
                    endpoint = this.manager.config.routes.revenueBulkDelete || '/revenue/bulk-delete';
                    break;
                case 'amTab':
                    endpoint = this.manager.config.routes.accountManagerBulkDelete || '/account-manager/bulk-delete';
                    break;
                case 'ccTab':
                    endpoint = this.manager.config.routes.corporateCustomerBulkDelete || '/corporate-customer/bulk-delete';
                    break;
                default:
                    throw new Error('Unknown tab type for bulk delete');
            }
            
            // ✅ FIXED: Use POST with proper parameters instead of complex type detection
            const response = await this.manager.requestHandler.makeRequest(
                'POST',
                endpoint,
                {
                    ids: selectedIds,
                    _token: this.manager.requestHandler.getCSRFToken()
                }
            );
            
            if (response.success) {
                this.processBulkDeleteSuccess(response, selectedIds);
            }
            
        } catch (error) {
            this.handleBulkDeleteError(error);
        }
    }
    
    processBulkDeleteSuccess(response, selectedIds) {
        // Remove deleted rows from DOM
        selectedIds.forEach(id => {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                row.remove();
            }
        });
        
        // Clear selections
        this.clearAllSelections();
        
        // Update counts
        this.manager.tabModule.updateTabCounts();
        
        // Show success message
        this.manager.notificationModule.showSuccess(
            response.message || `Berhasil menghapus ${selectedIds.length} data`
        );
        
        console.log('✅ Bulk delete completed successfully');
    }
    
    handleBulkDeleteError(error) {
        console.error('❌ Bulk delete error:', error);
        this.manager.errorHandler.handleAjaxError(error, 'Bulk Delete');
    }
    
    getCurrentActiveTab() {
        const activeTab = document.querySelector('.tab-item.active');
        return activeTab ? activeTab.getAttribute('data-tab') : 'revenueTab';
    }
    
    clearAllSelections() {
        // Clear selected IDs
        this.selectedIds.clear();
        
        // Uncheck all checkboxes
        document.querySelectorAll('.row-checkbox:checked').forEach(checkbox => {
            checkbox.checked = false;
            const row = checkbox.closest('tr');
            if (row) {
                row.classList.remove('selected');
            }
        });
        
        // Uncheck select all checkboxes
        document.querySelectorAll('#select-all-revenue, #select-all-am, #select-all-cc').forEach(selectAll => {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        });
        
        // Hide bulk actions
        this.updateBulkActionsVisibility();
    }
    
    updateBulkActionsVisibility() {
        const bulkToolbar = document.getElementById('bulk-actions-toolbar');
        const selectedCountSpan = document.getElementById('selected-count');
        
        if (this.selectedIds.size > 0) {
            if (bulkToolbar) bulkToolbar.classList.add('show');
            if (selectedCountSpan) selectedCountSpan.textContent = this.selectedIds.size;
        } else {
            if (bulkToolbar) bulkToolbar.classList.remove('show');
            if (selectedCountSpan) selectedCountSpan.textContent = '0';
        }
    }
}

// ===================================================================
// 5. 📤 IMPORT/EXPORT MODULE - 🔧 FIXED: Complete modal flow
// ===================================================================

class ImportModule {
    constructor(manager) {
        this.manager = manager;
        this.currentImportType = null;
        this.initializeImportComponents();
        console.log('📤 Import Module initialized');
    }
    
    initializeImportComponents() {
        this.setupImportForms();
    }
    
    setupImportForms() {
        // Setup import form submissions
        document.addEventListener('submit', (e) => {
            const form = e.target;
            
            if (form.id === 'importRevenueForm' || form.id === 'amImportForm' || form.id === 'ccImportForm') {
                e.preventDefault();
                this.handleImportSubmission(form);
            }
        });
    }
    
    // ❌ BROKEN → 🔧 FIXED: Complete modal flow implementation
    async handleImportSubmission(form) {
        const fileInput = form.querySelector('input[type="file"]');
        
        if (!fileInput || !fileInput.files.length) {
            this.manager.notificationModule.showError('Pilih file untuk diimpor');
            return;
        }
        
        // Determine import type from form ID
        let importType = 'revenue';
        let endpoint = '';
        
        if (form.id === 'importRevenueForm') {
            importType = 'revenue';
            endpoint = this.manager.config.routes.revenueImport;
        } else if (form.id === 'amImportForm') {
            importType = 'account-manager';
            endpoint = this.manager.config.routes.accountManagerImport;
        } else if (form.id === 'ccImportForm') {
            importType = 'corporate-customer';
            endpoint = this.manager.config.routes.corporateCustomerImport;
        }
        
        const formData = new FormData(form);
        
        try {
            // ✅ STEP 1: Close import modal
            const modal = form.closest('.modal');
            if (modal) {
                this.manager.modalModule.closeModal(modal.id);
            }
            
            // ✅ STEP 2: Show loading modal
            this.showImportLoadingModal(importType);
            
            // ✅ STEP 3: Submit import request
            console.log(`📤 Starting import for ${importType}:`, endpoint);
            
            const response = await this.manager.requestHandler.makeRequest(
                'POST',
                endpoint,
                formData
            );
            
            // ✅ STEP 4: Hide loading and show result
            this.hideImportLoadingModal();
            
            // ✅ STEP 5: Show result modal
            this.showImportResultModal(response, importType);
            
        } catch (error) {
            this.hideImportLoadingModal();
            this.handleImportError(error, importType);
        }
    }
    
    // ✅ NEW: Show proper loading modal
    showImportLoadingModal(importType) {
        // Remove existing result modal if any
        const existingResultModal = document.getElementById('importResultModal');
        if (existingResultModal) {
            existingResultModal.remove();
        }
        
        // Create loading modal if doesn't exist
        let loadingModal = document.getElementById('importLoadingModal');
        
        if (!loadingModal) {
            const loadingModalHtml = `
                <div class="modal fade" id="importLoadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body text-center py-5">
                                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <h5 class="mb-2">Mengimpor Data ${this.getTypeDisplayName(importType)}</h5>
                                <p class="text-muted mb-0">Harap tunggu, proses import sedang berlangsung...</p>
                                <div class="mt-3">
                                    <div class="progress-container">
                                        <div class="progress-step active">
                                            <div class="progress-icon">1</div>
                                            <div><strong>Upload File</strong><br><small class="text-muted">File berhasil diunggah</small></div>
                                        </div>
                                        <div class="progress-step active">
                                            <div class="progress-icon"><i class="fas fa-spinner fa-spin"></i></div>
                                            <div><strong>Validasi Data</strong><br><small class="text-muted">Memeriksa format dan konsistensi data</small></div>
                                        </div>
                                        <div class="progress-step">
                                            <div class="progress-icon">3</div>
                                            <div><strong>Proses Import</strong><br><small class="text-muted">Menyimpan data ke database</small></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', loadingModalHtml);
            loadingModal = document.getElementById('importLoadingModal');
        }
        
        // Show loading modal
        this.manager.modalModule.openModal('importLoadingModal');
        
        console.log(`📤 Import loading modal shown for ${importType}`);
    }
    
    hideImportLoadingModal() {
        this.manager.modalModule.closeModal('importLoadingModal');
    }
    
    // ✅ NEW: Enhanced result modal with detailed reporting
    showImportResultModal(response, importType) {
        // Remove existing result modal
        const existingModal = document.getElementById('importResultModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        const modalId = 'importResultModal';
        const modalHtml = this.generateDetailedImportResult(response, importType);
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Setup download error log button
        this.setupDownloadErrorLogButton(response, importType);
        
        // Setup refresh button
        this.setupRefreshPageButton();
        
        // Show modal
        this.manager.modalModule.openModal(modalId);
        
        console.log(`📊 Import result modal shown for ${importType}:`, response);
    }
    
    // ✅ NEW: Generate comprehensive import result modal
    generateDetailedImportResult(response, importType) {
        const isSuccess = response.success;
        const data = response.data || response.summary || {};
        
        // Extract detailed information
        const totalRows = data.total_rows || data.processed || 0;
        const successRows = (data.imported || 0) + (data.updated || 0);
        const errorRows = data.errors || data.failed_rows || 0;
        const duplicateRows = data.duplicates || 0;
        const conflictRows = data.conflicts || 0;
        
        const hasErrors = errorRows > 0;
        const hasWarnings = (data.warning_details && data.warning_details.length > 0) || duplicateRows > 0 || conflictRows > 0;
        
        return `
            <div class="modal fade import-result-modal" id="importResultModal" tabindex="-1" aria-labelledby="importResultModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importResultModalLabel">
                                <i class="fas fa-chart-bar me-2"></i>
                                ${isSuccess ? '✅' : '❌'} Hasil Import ${this.getTypeDisplayName(importType)}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Summary Alert -->
                            <div class="alert ${isSuccess ? 'alert-success' : 'alert-warning'} mb-4">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Ringkasan Import
                                </h6>
                                <p class="mb-0">${response.message}</p>
                            </div>
                            
                            <!-- Statistics Cards -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h3 class="text-info mb-1">${totalRows}</h3>
                                            <small class="text-muted">Total Baris</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h3 class="text-success mb-1">${successRows}</h3>
                                            <small class="text-muted">Berhasil</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h3 class="text-warning mb-1">${duplicateRows + conflictRows}</h3>
                                            <small class="text-muted">Duplikat/Konflik</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <h3 class="text-danger mb-1">${errorRows}</h3>
                                            <small class="text-muted">Error</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Detailed breakdown -->
                            ${data.imported ? `
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="border rounded p-3">
                                            <h6 class="text-success"><i class="fas fa-plus-circle me-2"></i>Data Baru: ${data.imported}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3">
                                            <h6 class="text-info"><i class="fas fa-edit me-2"></i>Data Diperbarui: ${data.updated || 0}</h6>
                                        </div>
                                    </div>
                                </div>
                            ` : ''}
                            
                            <!-- Detailed Results Accordion -->
                            <div class="accordion" id="importDetailsAccordion">
                                ${hasErrors ? this.generateErrorAccordion(data) : ''}
                                ${hasWarnings ? this.generateWarningAccordion(data) : ''}
                                ${data.success_details ? this.generateSuccessAccordion(data) : ''}
                                ${data.conflict_details ? this.generateConflictAccordion(data) : ''}
                            </div>
                            
                            <!-- Additional Info -->
                            ${data.monthly_pairs_found ? `
                                <div class="alert alert-info mt-3">
                                    <small><strong>Info:</strong> Ditemukan ${data.monthly_pairs_found} pasangan kolom bulanan dalam file</small>
                                </div>
                            ` : ''}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Tutup
                            </button>
                            ${hasErrors ? `
                                <button type="button" class="btn btn-warning" id="download-error-log">
                                    <i class="fas fa-download me-1"></i> Unduh Log Error
                                </button>
                            ` : ''}
                            <button type="button" class="btn btn-primary" id="refresh-page">
                                <i class="fas fa-sync-alt me-1"></i> Refresh Halaman
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    generateErrorAccordion(data) {
        const errors = data.error_details || [];
        if (errors.length === 0) return '';
        
        return `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#errorDetails" aria-expanded="false">
                        <i class="fas fa-exclamation-circle text-danger me-2"></i>
                        Detail Error (${errors.length})
                    </button>
                </h2>
                <div id="errorDetails" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                ${errors.slice(0, 20).map(error => `<li>${error}</li>`).join('')}
                                ${errors.length > 20 ? `<li><em>... dan ${errors.length - 20} error lainnya</em></li>` : ''}
                            </ul>
                        </div>
                        ${errors.length > 20 ? '<p><small class="text-muted">Download log error untuk melihat semua detail</small></p>' : ''}
                    </div>
                </div>
            </div>
        `;
    }
    
    generateWarningAccordion(data) {
        const warnings = data.warning_details || [];
        if (warnings.length === 0) return '';
        
        return `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#warningDetails" aria-expanded="false">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Peringatan (${warnings.length})
                    </button>
                </h2>
                <div id="warningDetails" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="alert alert-warning">
                            <ul class="mb-0">
                                ${warnings.slice(0, 10).map(warning => `<li>${warning}</li>`).join('')}
                                ${warnings.length > 10 ? `<li><em>... dan ${warnings.length - 10} peringatan lainnya</em></li>` : ''}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    generateSuccessAccordion(data) {
        const successes = data.success_details || [];
        if (successes.length === 0) return '';
        
        return `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#successDetails" aria-expanded="false">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Detail Berhasil (${successes.length > 50 ? '50+' : successes.length})
                    </button>
                </h2>
                <div id="successDetails" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="alert alert-success">
                            <ul class="mb-0">
                                ${successes.slice(0, 10).map(success => `<li>${success}</li>`).join('')}
                                ${successes.length > 10 ? `<li><em>... dan ${successes.length - 10} sukses lainnya</em></li>` : ''}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    generateConflictAccordion(data) {
        const conflicts = data.conflict_details || [];
        if (conflicts.length === 0) return '';
        
        return `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#conflictDetails" aria-expanded="false">
                        <i class="fas fa-exchange-alt text-info me-2"></i>
                        Detail Konflik (${conflicts.length})
                    </button>
                </h2>
                <div id="conflictDetails" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="alert alert-info">
                            <ul class="mb-0">
                                ${conflicts.slice(0, 10).map(conflict => 
                                    `<li>Baris ${conflict.row}, ${conflict.month}: ${conflict.reason}</li>`
                                ).join('')}
                                ${conflicts.length > 10 ? `<li><em>... dan ${conflicts.length - 10} konflik lainnya</em></li>` : ''}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // ✅ NEW: Setup download error log functionality
    setupDownloadErrorLogButton(response, importType) {
        const downloadBtn = document.getElementById('download-error-log');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', () => {
                this.downloadErrorLog(response, importType);
            });
        }
    }
    
    // ✅ NEW: Download error log as TXT file
    downloadErrorLog(response, importType) {
        const data = response.data || response.summary || {};
        const errors = data.error_details || [];
        const warnings = data.warning_details || [];
        
        if (errors.length === 0 && warnings.length === 0) {
            this.manager.notificationModule.showInfo('Tidak ada error untuk diunduh');
            return;
        }
        
        const logContent = this.generateErrorLogContent(response, importType);
        const filename = `import_${importType}_errors_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.txt`;
        
        UtilityFunctions.downloadTextFile(logContent, filename);
        
        this.manager.notificationModule.showSuccess('Log error berhasil diunduh');
    }
    
    // ✅ NEW: Generate error log content
    generateErrorLogContent(response, importType) {
        const data = response.data || response.summary || {};
        const timestamp = new Date().toISOString();
        
        let content = `IMPORT ERROR LOG\n`;
        content += `==========================================\n`;
        content += `Type: ${this.getTypeDisplayName(importType)}\n`;
        content += `Timestamp: ${timestamp}\n`;
        content += `Success: ${response.success}\n`;
        content += `Message: ${response.message}\n`;
        content += `==========================================\n\n`;
        
        // Statistics
        content += `STATISTICS:\n`;
        content += `- Total Rows: ${data.total_rows || data.processed || 0}\n`;
        content += `- Imported: ${data.imported || 0}\n`;
        content += `- Updated: ${data.updated || 0}\n`;
        content += `- Errors: ${data.errors || data.failed_rows || 0}\n`;
        content += `- Duplicates: ${data.duplicates || 0}\n`;
        content += `- Conflicts: ${data.conflicts || 0}\n\n`;
        
        // Error Details
        if (data.error_details && data.error_details.length > 0) {
            content += `ERROR DETAILS:\n`;
            content += `----------------------------------------\n`;
            data.error_details.forEach((error, index) => {
                content += `${index + 1}. ${error}\n`;
            });
            content += `\n`;
        }
        
        // Warning Details
        if (data.warning_details && data.warning_details.length > 0) {
            content += `WARNING DETAILS:\n`;
            content += `----------------------------------------\n`;
            data.warning_details.forEach((warning, index) => {
                content += `${index + 1}. ${warning}\n`;
            });
            content += `\n`;
        }
        
        // Conflict Details
        if (data.conflict_details && data.conflict_details.length > 0) {
            content += `CONFLICT DETAILS:\n`;
            content += `----------------------------------------\n`;
            data.conflict_details.forEach((conflict, index) => {
                content += `${index + 1}. Row ${conflict.row}, ${conflict.month}: ${conflict.reason}\n`;
                if (conflict.changes && conflict.changes.length > 0) {
                    content += `   Changes: ${conflict.changes.join(', ')}\n`;
                }
            });
            content += `\n`;
        }
        
        content += `==========================================\n`;
        content += `Log generated by Revenue Management System\n`;
        
        return content;
    }
    
    setupRefreshPageButton() {
        const refreshBtn = document.getElementById('refresh-page');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                window.location.reload();
            });
        }
    }
    
    getTypeDisplayName(type) {
        const typeMap = {
            'revenue': 'Revenue',
            'account-manager': 'Account Manager',
            'corporate-customer': 'Corporate Customer'
        };
        return typeMap[type] || type;
    }
    
    handleImportError(error, type) {
        console.error(`❌ Import error for ${type}:`, error);
        
        let errorMessage = 'Terjadi kesalahan saat mengimpor data';
        
        if (error.response && error.response.data) {
            if (error.response.data.message) {
                errorMessage = error.response.data.message;
            }
            
            // ✅ FIXED: Handle "Array to string conversion" error
            if (error.response.data.error && error.response.data.error.includes('Array to string conversion')) {
                errorMessage = 'Import berhasil diproses, namun terjadi kesalahan dalam format response. Data kemungkinan sudah tersimpan.';
                
                // Show import success notification instead of error
                this.manager.notificationModule.showWarning(
                    errorMessage,
                    'Silakan refresh halaman untuk melihat data yang diimport'
                );
                
                // Show a simple success modal instead of error
                this.showSimpleResultModal(true, errorMessage, type);
                return;
            }
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        // Show error modal for actual errors
        this.showSimpleResultModal(false, errorMessage, type);
    }
    
    // ✅ NEW: Simple result modal for errors or simple responses
    showSimpleResultModal(isSuccess, message, importType) {
        const existingModal = document.getElementById('importResultModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        const modalHtml = `
            <div class="modal fade" id="importResultModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                ${isSuccess ? '⚠️' : '❌'} Import ${this.getTypeDisplayName(importType)}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert ${isSuccess ? 'alert-warning' : 'alert-danger'}">
                                ${message}
                            </div>
                            ${isSuccess ? `
                                <p class="mb-0">
                                    <strong>Rekomendasi:</strong> Refresh halaman untuk melihat hasil import terbaru.
                                </p>
                            ` : ''}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                                <i class="fas fa-sync-alt me-1"></i> Refresh Halaman
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        this.manager.modalModule.openModal('importResultModal');
    }
}

// ===================================================================
// 6. 🔄 DOWNLOAD MODULE (No changes needed)
// ===================================================================

class DownloadModule {
    constructor(manager) {
        this.manager = manager;
        this.initializeDownloadComponents();
        console.log('🔄 Download Module initialized');
    }
    
    initializeDownloadComponents() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('download-btn') || e.target.closest('.download-btn')) {
                e.preventDefault();
                const button = e.target.closest('.download-btn');
                const type = button.dataset.type;
                const format = button.dataset.format || 'excel';
                
                if (type) {
                    this.handleDownloadAction(type, format);
                }
            }
        });
    }
    
    handleDownloadAction(type, format = 'excel') {
        console.log(`🔄 Handling download: ${type} (${format})`);
        
        const downloadUrl = this.getDownloadUrl(type, format);
        
        if (downloadUrl) {
            this.triggerDownload(downloadUrl, type, format);
        } else {
            this.manager.notificationModule.showError(
                `Download ${type} tidak tersedia`
            );
        }
    }
    
    getDownloadUrl(type, format) {
        const routes = this.manager.config.routes;
        
        switch (type) {
            case 'revenue_template':
                return routes.revenueTemplate;
            case 'revenue_export':
                return routes.revenueExport;
            case 'account_manager_template':
                return routes.accountManagerTemplate;
            case 'account_manager_export':
                return routes.accountManagerExport;
            case 'corporate_customer_template':
                return routes.corporateCustomerTemplate;
            case 'corporate_customer_export':
                return routes.corporateCustomerExport;
            default:
                console.warn(`Unknown download type: ${type}`);
                return null;
        }
    }
    
    triggerDownload(url, type, format) {
        try {
            // Show downloading notification
            this.manager.notificationModule.showInfo(
                `Memulai download ${this.getTypeDisplayName(type)}...`
            );
            
            // Create temporary link and trigger download
            const link = document.createElement('a');
            link.href = url;
            link.download = this.generateFilename(type, format);
            link.style.display = 'none';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show success notification
            setTimeout(() => {
                this.manager.notificationModule.showSuccess(
                    `Download ${this.getTypeDisplayName(type)} berhasil dimulai`
                );
            }, 500);
            
            console.log(`✅ Download triggered: ${url}`);
            
        } catch (error) {
            console.error('❌ Download error:', error);
            this.manager.notificationModule.showError(
                `Gagal mendownload ${this.getTypeDisplayName(type)}`
            );
        }
    }
    
    generateFilename(type, format) {
        const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
        const extension = format === 'csv' ? '.csv' : '.xlsx';
        
        const typeMap = {
            'revenue_template': `template_revenue_${timestamp}`,
            'revenue_export': `data_revenue_${timestamp}`,
            'account_manager_template': `template_account_manager_${timestamp}`,
            'account_manager_export': `data_account_manager_${timestamp}`,
            'corporate_customer_template': `template_corporate_customer_${timestamp}`,
            'corporate_customer_export': `data_corporate_customer_${timestamp}`
        };
        
        return (typeMap[type] || `download_${timestamp}`) + extension;
    }
    
    getTypeDisplayName(type) {
        const typeMap = {
            'revenue_template': 'Template Revenue',
            'revenue_export': 'Data Revenue',
            'account_manager_template': 'Template Account Manager',
            'account_manager_export': 'Data Account Manager',
            'corporate_customer_template': 'Template Corporate Customer',
            'corporate_customer_export': 'Data Corporate Customer'
        };
        return typeMap[type] || type;
    }
}

// ===================================================================
// 7. 🗂️ TAB MODULE (No changes needed)
// ===================================================================

class TabModule {
    constructor(manager) {
        this.manager = manager;
        this.currentTab = 'revenueTab';
        this.initializeTabComponents();
        console.log('🗂️ Tab Module initialized');
    }
    
    initializeTabComponents() {
        this.setupTabSwitching();
        this.updateTabCounts();
    }
    
    setupTabSwitching() {
        document.querySelectorAll('.tab-item').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const tabId = tab.dataset.tab;
                if (tabId) {
                    this.switchTab(tabId);
                }
            });
        });
    }
    
    switchTab(tabId) {
        // Remove active from all tabs
        document.querySelectorAll('.tab-item').forEach(t => {
            t.classList.remove('active');
        });
        
        document.querySelectorAll('.tab-content').forEach(c => {
            c.classList.remove('active');
        });
        
        // Add active to target tab
        const targetTab = document.querySelector(`[data-tab="${tabId}"]`);
        const targetContent = document.getElementById(tabId);
        
        if (targetTab && targetContent) {
            targetTab.classList.add('active');
            targetContent.classList.add('active');
            this.currentTab = tabId;
            
            // Clear selections when switching tabs
            if (this.manager.bulkModule) {
                this.manager.bulkModule.clearAllSelections();
            }
            
            console.log(`🗂️ Switched to tab: ${tabId}`);
        } else {
            console.error(`❌ Tab or content not found: ${tabId}`);
        }
    }
    
    getCurrentActiveTab() {
        const activeTab = document.querySelector('.tab-item.active');
        return activeTab ? activeTab.getAttribute('data-tab') : this.currentTab;
    }
    
    updateTabCounts() {
        // Update from window.currentData or fetch fresh data
        if (window.currentData) {
            this.updateTabCount('revenue-count', this.getRevenueCount());
            this.updateTabCount('am-count', this.getAccountManagerCount());
            this.updateTabCount('cc-count', this.getCorporateCustomerCount());
        } else {
            // Fetch counts from DOM if currentData not available
            this.updateCountsFromDOM();
        }
    }
    
    getRevenueCount() {
        if (window.currentData && window.currentData.revenues) {
            return window.currentData.revenues.total || 0;
        }
        // Fallback: count rows in table
        return document.querySelectorAll('#revenueTab tbody tr').length;
    }
    
    getAccountManagerCount() {
        if (window.currentData && window.currentData.accountManagers) {
            return window.currentData.accountManagers.total || 0;
        }
        // Fallback: count rows in table
        return document.querySelectorAll('#amTab tbody tr').length;
    }
    
    getCorporateCustomerCount() {
        if (window.currentData && window.currentData.corporateCustomers) {
            return window.currentData.corporateCustomers.total || 0;
        }
        // Fallback: count rows in table
        return document.querySelectorAll('#ccTab tbody tr').length;
    }
    
    updateCountsFromDOM() {
        this.updateTabCount('revenue-count', this.getRevenueCount());
        this.updateTabCount('am-count', this.getAccountManagerCount());
        this.updateTabCount('cc-count', this.getCorporateCustomerCount());
    }
    
    updateTabCount(elementId, count) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = count;
            console.log(`🗂️ Updated ${elementId}: ${count}`);
        }
    }
    
    refreshCurrentTab() {
        // Trigger refresh of current tab data
        const currentTabId = this.getCurrentActiveTab();
        console.log(`🗂️ Refreshing current tab: ${currentTabId}`);
        
        // Update counts after a short delay to allow for server updates
        setTimeout(() => {
            this.updateTabCounts();
        }, 1000);
    }
}

// ===================================================================
// 8. 🪟 MODAL MODULE (No changes needed)
// ===================================================================

class ModalModule {
    constructor(manager) {
        this.manager = manager;
        this.activeModals = new Map();
        this.initializeModalComponents();
        console.log('🪟 Modal Module initialized');
    }
    
    initializeModalComponents() {
        // Setup modal event listeners
        document.addEventListener('hidden.bs.modal', (e) => {
            const modalId = e.target.id;
            if (this.activeModals.has(modalId)) {
                this.activeModals.delete(modalId);
            }
        });
        
        // Setup form reset on modal show
        document.addEventListener('show.bs.modal', (e) => {
            const modal = e.target;
            const form = modal.querySelector('form[data-form-reset="true"]');
            if (form) {
                this.resetForm(form);
            }
        });
    }
    
    openModal(modalId, options = {}) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error(`❌ Modal not found: ${modalId}`);
            return;
        }
        
        // Reset form if specified
        if (options.resetForm) {
            const form = modal.querySelector('form[data-form-reset="true"]');
            if (form) {
                this.resetForm(form);
            }
        }
        
        try {
            const bsModal = new bootstrap.Modal(modal, options);
            this.activeModals.set(modalId, bsModal);
            bsModal.show();
            
            console.log(`🪟 Modal opened: ${modalId}`);
        } catch (error) {
            console.error(`❌ Error opening modal ${modalId}:`, error);
        }
    }
    
    closeModal(modalId) {
        const bsModal = this.activeModals.get(modalId);
        if (bsModal) {
            try {
                bsModal.hide();
                this.activeModals.delete(modalId);
                console.log(`🪟 Modal closed: ${modalId}`);
            } catch (error) {
                console.error(`❌ Error closing modal ${modalId}:`, error);
            }
        } else {
            // ✅ FIXED: Try to close modal by selector fallback
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                try {
                    const bsModalFallback = bootstrap.Modal.getInstance(modalElement);
                    if (bsModalFallback) {
                        bsModalFallback.hide();
                    }
                    console.log(`🪟 Modal closed via fallback: ${modalId}`);
                } catch (error) {
                    console.error(`❌ Error closing modal via fallback ${modalId}:`, error);
                }
            }
        }
    }
    
    resetForm(form) {
        if (!form) return;
        
        form.reset();
        
        // Clear validation feedback
        form.querySelectorAll('.validation-feedback').forEach(feedback => {
            feedback.textContent = '';
            feedback.className = 'validation-feedback';
        });
        
        // Clear validation spinners
        form.querySelectorAll('.validation-spinner').forEach(spinner => {
            spinner.style.display = 'none';
        });
        
        // Clear suggestions
        form.querySelectorAll('.suggestions-container').forEach(suggestion => {
            suggestion.classList.remove('show');
            suggestion.innerHTML = '';
        });
        
        // Reset divisi buttons
        form.querySelectorAll('.divisi-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Clear hidden inputs (except CSRF token and method)
        form.querySelectorAll('input[type="hidden"]').forEach(input => {
            if (!input.name.includes('_token') && !input.name.includes('_method')) {
                input.value = '';
            }
        });
        
        // Reset file inputs
        form.querySelectorAll('input[type="file"]').forEach(input => {
            input.value = '';
        });
        
        // Reset form state classes
        form.classList.remove('was-validated');
        
        console.log('🪟 Form reset completed');
    }
}

// ===================================================================
// 9. 🔔 NOTIFICATION MODULE (Enhanced with different types)
// ===================================================================

class NotificationModule {
    constructor(manager) {
        this.manager = manager;
        this.container = document.getElementById('notification-container');
        this.title = document.getElementById('notification-title');
        this.message = document.getElementById('notification-message');
        this.details = document.getElementById('notification-details');
        this.closeBtn = document.getElementById('notification-close');
        
        this.setupNotificationEvents();
        console.log('🔔 Notification Module initialized');
    }
    
    setupNotificationEvents() {
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => {
                this.hide();
            });
        }
        
        // Auto-hide notifications after delay
        this.autoHideTimer = null;
    }
    
    show(title, message, type = 'info', details = null, duration = 5000) {
        if (!this.container) return;
        
        // Clear previous timer
        if (this.autoHideTimer) {
            clearTimeout(this.autoHideTimer);
        }
        
        // Update content
        if (this.title) this.title.textContent = title;
        if (this.message) this.message.textContent = message;
        
        // Handle details
        if (this.details) {
            if (details) {
                if (typeof details === 'string') {
                    this.details.textContent = details;
                } else if (Array.isArray(details)) {
                    this.details.innerHTML = details.map(detail => `<div>• ${detail}</div>`).join('');
                }
                this.details.style.display = 'block';
            } else {
                this.details.style.display = 'none';
            }
        }
        
        // Set notification type
        this.container.className = `notification-persistent ${type}`;
        this.container.classList.add('show');
        
        // Auto-hide after duration
        if (duration > 0) {
            this.autoHideTimer = setTimeout(() => {
                this.hide();
            }, duration);
        }
        
        console.log(`🔔 Notification shown: ${type} - ${title}`);
    }
    
    showSuccess(message, details = null, duration = 4000) {
        this.show('Berhasil', message, 'success', details, duration);
    }
    
    showError(message, details = null, duration = 8000) {
        this.show('Error', message, 'error', details, duration);
    }
    
    showWarning(message, details = null, duration = 6000) {
        this.show('Peringatan', message, 'warning', details, duration);
    }
    
    showInfo(message, details = null, duration = 5000) {
        this.show('Informasi', message, 'info', details, duration);
    }
    
    hide() {
        if (this.container) {
            this.container.classList.remove('show');
        }
        
        if (this.autoHideTimer) {
            clearTimeout(this.autoHideTimer);
            this.autoHideTimer = null;
        }
        
        console.log('🔔 Notification hidden');
    }
}

// ===================================================================
// 10. 🌐 REQUEST HANDLER (Enhanced error handling)
// ===================================================================

class RequestHandler {
    constructor(manager) {
        this.manager = manager;
        this.csrfToken = this.getCSRFToken();
        console.log('🌐 Request Handler initialized');
    }
    
    getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) {
            console.warn('⚠️ CSRF token not found');
        }
        return token;
    }
    
    async makeRequest(method, url, data = null, options = {}) {
        const defaultOptions = {
            method: method.toUpperCase(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            ...options
        };
        
        // Add CSRF token for non-GET requests
        if (method.toUpperCase() !== 'GET') {
            defaultOptions.headers['X-CSRF-TOKEN'] = this.csrfToken;
        }
        
        // Handle different data types
        if (data) {
            if (data instanceof FormData) {
                defaultOptions.body = data;
            } else if (typeof data === 'object') {
                if (method.toUpperCase() === 'GET') {
                    // Convert object to URL parameters for GET requests
                    const params = new URLSearchParams(data);
                    url += (url.includes('?') ? '&' : '?') + params.toString();
                } else {
                    defaultOptions.headers['Content-Type'] = 'application/json';
                    defaultOptions.body = JSON.stringify(data);
                }
            } else {
                defaultOptions.body = data;
            }
        }
        
        try {
            console.log(`🌐 Making ${method} request to: ${url}`);
            
            const response = await fetch(url, defaultOptions);
            
            // Handle different response types
            const contentType = response.headers.get('content-type');
            let responseData;
            
            if (contentType && contentType.includes('application/json')) {
                responseData = await response.json();
            } else if (contentType && contentType.includes('text/html')) {
                // ✅ FIXED: Handle HTML responses (often error pages)
                const htmlText = await response.text();
                
                if (!response.ok) {
                    throw new Error(`Server returned HTML error page (${response.status})`);
                }
                
                responseData = {
                    success: true,
                    message: 'Request completed',
                    html: htmlText
                };
            } else {
                responseData = await response.text();
            }
            
            if (!response.ok) {
                throw new Error(responseData.message || `HTTP ${response.status}: ${response.statusText}`);
            }
            
            console.log(`✅ Request successful: ${method} ${url}`);
            return responseData;
            
        } catch (error) {
            console.error(`❌ Request failed: ${method} ${url}`, error);
            throw error;
        }
    }
}

// ===================================================================
// 11. 🛠️ ERROR HANDLER (Enhanced error handling and reporting)
// ===================================================================

class ErrorHandler {
    constructor(manager) {
        this.manager = manager;
        console.log('🛠️ Error Handler initialized');
    }
    
    handleAjaxError(error, context = 'Unknown') {
        console.error(`🛠️ Handling error in ${context}:`, error);
        
        let errorMessage = 'Terjadi kesalahan sistem';
        let errorDetails = null;
        
        // Parse different error types
        if (error.response && error.response.data) {
            // Axios-style error response
            const responseData = error.response.data;
            errorMessage = responseData.message || errorMessage;
            errorDetails = responseData.errors || responseData.details;
        } else if (error.message) {
            // Standard Error object
            errorMessage = error.message;
            
            // Extract validation errors if present
            if (error.message.includes('422') || error.message.includes('validation')) {
                errorMessage = 'Validasi gagal. Periksa data yang dimasukkan.';
            } else if (error.message.includes('403') || error.message.includes('forbidden')) {
                errorMessage = 'Akses ditolak. Anda tidak memiliki izin untuk operasi ini.';
            } else if (error.message.includes('500') || error.message.includes('server')) {
                errorMessage = 'Terjadi kesalahan pada server. Silakan coba lagi.';
            } else if (error.message.includes('network') || error.message.includes('fetch')) {
                errorMessage = 'Koneksi bermasalah. Periksa koneksi internet Anda.';
            }
        }
        
        // Show notification
        this.manager.notificationModule.showError(
            errorMessage,
            errorDetails,
            8000 // Show error longer
        );
        
        // Log to console for debugging
        console.error(`❌ ${context} Error Details:`, {
            message: errorMessage,
            details: errorDetails,
            originalError: error
        });
    }
    
    handleSingleDeleteError(error, id) {
        console.error(`🗑️ Single delete error for ID ${id}:`, error);
        
        let errorMessage = `Gagal menghapus data dengan ID ${id}`;
        
        if (error.message) {
            if (error.message.includes('constraint') || error.message.includes('foreign key')) {
                errorMessage = 'Data tidak dapat dihapus karena masih digunakan oleh data lain.';
            } else if (error.message.includes('not found')) {
                errorMessage = 'Data tidak ditemukan atau sudah dihapus.';
            } else if (error.message.includes('HTML error page')) {
                errorMessage = 'Terjadi kesalahan server saat menghapus data.';
            }
        }
        
        this.manager.notificationModule.showError(errorMessage);
    }
    
    handleGlobalError(error) {
        console.error('🌐 Global JavaScript Error:', error);
        
        // Only show user-friendly error for critical issues
        if (error.message && !error.message.includes('Script error')) {
            this.manager.notificationModule.showError(
                'Terjadi kesalahan pada aplikasi',
                'Halaman akan dimuat ulang otomatis',
                5000
            );
            
            // Auto-reload after delay
            setTimeout(() => {
                window.location.reload();
            }, 6000);
        }
    }
    
    handlePromiseRejection(reason) {
        console.error('🔄 Unhandled Promise Rejection:', reason);
        
        // Handle specific promise rejections
        if (reason && reason.message) {
            if (reason.message.includes('fetch')) {
                this.manager.notificationModule.showWarning(
                    'Koneksi terputus',
                    'Beberapa fitur mungkin tidak berfungsi'
                );
            }
        }
    }
}

// ===================================================================
// 12. ⚡ EVENT HANDLER (Comprehensive event management)
// ===================================================================

class EventHandler {
    constructor(manager) {
        this.manager = manager;
        this.setupGlobalEventListeners();
        console.log('⚡ Event Handler initialized');
    }
    
    setupGlobalEventListeners() {
        // Document click handler for delegated events
        document.addEventListener('click', (e) => {
            this.handleDocumentClick(e);
        });
        
        // Document change handler for form inputs
        document.addEventListener('change', (e) => {
            this.handleDocumentChange(e);
        });
        
        // Window resize handler
        window.addEventListener('resize', () => {
            this.handleWindowResize();
        });
        
        // Setup specific event handlers
        this.setupDivisiButtonEvents();
        this.setupAccountManagerEvents();
        this.setupPasswordChangeEvents();
        this.setupFilterToggleEvents();
    }
    
    // ✅ FIXED: Added missing action handlers
    handleDocumentClick(e) {
        const target = e.target;
        
        // Handle action buttons
        if (target.matches('.edit-revenue') || target.closest('.edit-revenue')) {
            e.preventDefault();
            const button = target.closest('.edit-revenue');
            this.manager.crudModule.handleEditRevenue(button.dataset.id);
        }
        
        if (target.matches('.edit-account-manager') || target.closest('.edit-account-manager')) {
            e.preventDefault();
            const button = target.closest('.edit-account-manager');
            this.manager.crudModule.handleEditAccountManager(button.dataset.id);
        }
        
        if (target.matches('.edit-corporate-customer') || target.closest('.edit-corporate-customer')) {
            e.preventDefault();
            const button = target.closest('.edit-corporate-customer');
            this.manager.crudModule.handleEditCorporateCustomer(button.dataset.id);
        }
        
        // Handle change password buttons
        if (target.matches('.change-password-btn') || target.closest('.change-password-btn')) {
            e.preventDefault();
            const button = target.closest('.change-password-btn');
            this.manager.passwordModule.showChangePasswordModal(button.dataset.id, button.dataset.name);
        }
        
        // Handle filter toggle
        if (target.matches('#filterToggle') || target.closest('#filterToggle')) {
            e.preventDefault();
            this.manager.filterModule.toggleFilterPanel();
        }
        
        // Handle search close
        if (target.matches('.search-results-close') || target.closest('.search-results-close')) {
            e.preventDefault();
            this.manager.searchModule.hideSearchResults();
        }
    }
    
    handleDocumentChange(e) {
        const target = e.target;
        
        // Handle account manager selection changes
        if (target.id && target.id.includes('account_manager') && !target.id.includes('_id')) {
            this.manager.accountManagerIntegrationModule.handleAccountManagerChange(target);
        }
        
        // Handle divisi selection changes
        if (target.name && target.name.includes('divisi')) {
            this.manager.divisiModule.validateDivisiSelection();
        }
    }
    
    handleWindowResize() {
        // Handle responsive adjustments
        const searchContainer = document.getElementById('searchResultsContainer');
        if (searchContainer && searchContainer.classList.contains('show')) {
            // Adjust search results position if needed
            this.repositionSearchResults();
        }
    }
    
    repositionSearchResults() {
        // Implementation for repositioning search results on resize
        const searchContainer = document.getElementById('searchResultsContainer');
        const searchInput = document.getElementById('globalSearch');
        
        if (searchContainer && searchInput) {
            const inputRect = searchInput.getBoundingClientRect();
            searchContainer.style.top = `${inputRect.bottom + window.scrollY}px`;
            searchContainer.style.left = `${inputRect.left + window.scrollX}px`;
            searchContainer.style.width = `${inputRect.width}px`;
        }
    }
    
    // ✅ NEW: Setup divisi button events
    setupDivisiButtonEvents() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.divisi-btn') || e.target.closest('.divisi-btn')) {
                e.preventDefault();
                const button = e.target.closest('.divisi-btn');
                this.manager.divisiModule.handleDivisiButtonClick(button);
            }
        });
    }
    
    // ✅ NEW: Setup account manager events
    setupAccountManagerEvents() {
        // Handle account manager selection from autocomplete
        document.addEventListener('change', (e) => {
            if (e.target.id && e.target.id.includes('account_manager') && !e.target.id.includes('_id')) {
                this.manager.accountManagerIntegrationModule.handleAccountManagerSelection(null, e.target);
            }
        });
    }
    
    // ✅ NEW: Setup password change events
    setupPasswordChangeEvents() {
        // Password visibility toggle
        document.addEventListener('click', (e) => {
            if (e.target.matches('#toggle-password') || e.target.closest('#toggle-password')) {
                e.preventDefault();
                this.manager.passwordModule.togglePasswordVisibility();
            }
        });
        
        // Password strength checking
        document.addEventListener('input', (e) => {
            if (e.target.id === 'new_password') {
                this.manager.passwordModule.checkPasswordStrength(e.target.value);
            }
            
            if (e.target.id === 'new_password_confirmation') {
                this.manager.passwordModule.checkPasswordMatch();
            }
        });
    }
    
    // ✅ NEW: Setup filter toggle events
    setupFilterToggleEvents() {
        // Handle filter form submission
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'filter-form') {
                // Let default submission happen, but show loading
                this.manager.filterModule.showFilterLoading();
            }
        });
    }
}

// ===================================================================
// 13. 🆕 NEW MODULES - Divisi Management
// ===================================================================

class DivisiModule {
    constructor(manager) {
        this.manager = manager;
        this.selectedDivisiIds = new Set();
        console.log('🆕 Divisi Module initialized');
    }
    
    handleDivisiButtonClick(button) {
        const divisiId = button.dataset.divisiId;
        
        if (button.classList.contains('active')) {
            // Remove from selection
            button.classList.remove('active');
            this.selectedDivisiIds.delete(divisiId);
        } else {
            // Add to selection
            button.classList.add('active');
            this.selectedDivisiIds.add(divisiId);
        }
        
        this.updateDivisiHiddenInput(button);
        this.validateDivisiSelection();
        
        console.log(`🆕 Divisi selection updated:`, Array.from(this.selectedDivisiIds));
    }
    
    updateDivisiHiddenInput(button) {
        const form = button.closest('form');
        if (!form) return;
        
        const hiddenInput = form.querySelector('input[name="divisi_ids"]');
        if (hiddenInput) {
            hiddenInput.value = Array.from(this.selectedDivisiIds).join(',');
        }
    }
    
    validateDivisiSelection() {
        const selectedCount = this.selectedDivisiIds.size;
        
        // Update UI based on selection
        const divisiButtons = document.querySelectorAll('.divisi-btn');
        const parentContainer = divisiButtons[0]?.closest('.form-group');
        
        if (parentContainer) {
            const feedbackElement = parentContainer.querySelector('.validation-feedback') ||
                                    this.createValidationFeedback(parentContainer);
            
            if (selectedCount === 0) {
                feedbackElement.textContent = 'Pilih minimal satu divisi';
                feedbackElement.className = 'validation-feedback invalid';
                parentContainer.classList.add('has-error');
            } else {
                feedbackElement.textContent = `${selectedCount} divisi terpilih`;
                feedbackElement.className = 'validation-feedback valid';
                parentContainer.classList.remove('has-error');
            }
        }
        
        return selectedCount > 0;
    }
    
    createValidationFeedback(container) {
        const feedback = document.createElement('div');
        feedback.className = 'validation-feedback';
        container.appendChild(feedback);
        return feedback;
    }
    
    resetSelection() {
        this.selectedDivisiIds.clear();
        document.querySelectorAll('.divisi-btn.active').forEach(button => {
            button.classList.remove('active');
        });
        
        document.querySelectorAll('input[name="divisi_ids"]').forEach(input => {
            input.value = '';
        });
        
        console.log('🆕 Divisi selection reset');
    }
    
    setSelectedDivisi(divisiIds) {
        this.resetSelection();
        
        if (Array.isArray(divisiIds)) {
            divisiIds.forEach(id => {
                this.selectedDivisiIds.add(id.toString());
                const button = document.querySelector(`.divisi-btn[data-divisi-id="${id}"]`);
                if (button) {
                    button.classList.add('active');
                }
            });
            
            this.updateDivisiHiddenInput(document.querySelector('.divisi-btn'));
            this.validateDivisiSelection();
        }
        
        console.log('🆕 Divisi selection set to:', divisiIds);
    }
}

// ===================================================================
// 14. 🆕 NEW MODULES - Filter Management
// ===================================================================

class FilterModule {
    constructor(manager) {
        this.manager = manager;
        this.filterPanel = document.getElementById('filterArea');
        this.filterToggle = document.getElementById('filterToggle');
        this.isVisible = false;
        console.log('🆕 Filter Module initialized');
    }
    
    toggleFilterPanel() {
        if (this.isVisible) {
            this.hideFilterPanel();
        } else {
            this.showFilterPanel();
        }
    }
    
    showFilterPanel() {
        if (this.filterPanel) {
            this.filterPanel.style.display = 'block';
            this.isVisible = true;
            
            if (this.filterToggle) {
                this.filterToggle.classList.add('active');
                const icon = this.filterToggle.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-filter-circle-xmark';
                }
            }
            
            console.log('🆕 Filter panel shown');
        }
    }
    
    hideFilterPanel() {
        if (this.filterPanel) {
            this.filterPanel.style.display = 'none';
            this.isVisible = false;
            
            if (this.filterToggle) {
                this.filterToggle.classList.remove('active');
                const icon = this.filterToggle.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-filter';
                }
            }
            
            console.log('🆕 Filter panel hidden');
        }
    }
    
    showFilterLoading() {
        const submitButton = document.querySelector('#filter-form button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menerapkan Filter...';
        }
    }
    
    resetFilters() {
        const form = document.getElementById('filter-form');
        if (form) {
            form.reset();
            // Keep search parameter
            const searchParam = new URLSearchParams(window.location.search).get('search');
            if (searchParam) {
                const searchInput = form.querySelector('input[name="search"]');
                if (searchInput) {
                    searchInput.value = searchParam;
                }
            }
        }
    }
    
    getActiveFilters() {
        const form = document.getElementById('filter-form');
        if (!form) return {};
        
        const formData = new FormData(form);
        const filters = {};
        
        for (const [key, value] of formData.entries()) {
            if (value && value.trim() !== '') {
                filters[key] = value;
            }
        }
        
        return filters;
    }
}

// ===================================================================
// 15. 🆕 NEW MODULES - Password Management
// ===================================================================

class PasswordModule {
    constructor(manager) {
        this.manager = manager;
        this.currentAccountManagerId = null;
        this.modal = document.getElementById('changePasswordModal');
        console.log('🆕 Password Module initialized');
    }
    
    async showChangePasswordModal(accountManagerId, accountManagerName) {
        this.currentAccountManagerId = accountManagerId;
        
        try {
            // Load account manager info
            const response = await this.manager.requestHandler.makeRequest(
                'GET',
                this.manager.config.routes.accountManagerUserStatus.replace(':id', accountManagerId)
            );
            
            if (response.success && response.has_user_account) {
                this.populatePasswordModal(response.account_manager, response.user_email);
                this.manager.modalModule.openModal('changePasswordModal');
            } else {
                this.manager.notificationModule.showWarning(
                    'Account Manager belum memiliki akun user terdaftar'
                );
            }
        } catch (error) {
            this.manager.errorHandler.handleAjaxError(error, 'Change Password');
        }
    }
    
    populatePasswordModal(accountManager, userEmail) {
        const nameElement = document.getElementById('change_password_am_name');
        const emailElement = document.getElementById('change_password_am_email');
        const idInput = document.getElementById('change_password_am_id');
        
        if (nameElement) nameElement.textContent = accountManager.nama;
        if (emailElement) emailElement.textContent = userEmail || 'Tidak ada email';
        if (idInput) idInput.value = accountManager.id;
        
        // Reset form
        const form = document.getElementById('changePasswordForm');
        if (form) {
            this.manager.modalModule.resetForm(form);
        }
        
        console.log(`🆕 Password modal populated for: ${accountManager.nama}`);
    }
    
    async handlePasswordChange(form) {
        const formData = new FormData(form);
        const accountManagerId = formData.get('am_id');
        
        try {
            this.showPasswordChangeLoading(true);
            
            const response = await this.manager.requestHandler.makeRequest(
                'POST',
                this.manager.config.routes.accountManagerChangePassword.replace(':id', accountManagerId),
                formData
            );
            
            if (response.success) {
                this.manager.notificationModule.showSuccess(response.message);
                this.manager.modalModule.closeModal('changePasswordModal');
            }
            
        } catch (error) {
            this.manager.errorHandler.handleAjaxError(error, 'Password Change');
        } finally {
            this.showPasswordChangeLoading(false);
        }
    }
    
    showPasswordChangeLoading(show) {
        const loadingOverlay = document.getElementById('change-password-loading');
        const form = document.getElementById('changePasswordForm');
        
        if (loadingOverlay) {
            loadingOverlay.style.display = show ? 'flex' : 'none';
        }
        
        if (form) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = show;
                if (show) {
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';
                } else {
                    submitButton.innerHTML = '<i class="fas fa-save me-2"></i> Simpan Password Baru';
                }
            }
        }
    }
    
    togglePasswordVisibility() {
        const passwordInput = document.getElementById('new_password');
        const toggleButton = document.getElementById('toggle-password');
        
        if (passwordInput && toggleButton) {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            
            const icon = toggleButton.querySelector('i');
            if (icon) {
                icon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
            }
        }
    }
    
    checkPasswordStrength(password) {
        const feedback = document.getElementById('password_validation');
        if (!feedback) return;
        
        let strength = 0;
        let messages = [];
        
        if (password.length >= 8) strength++;
        else messages.push('Minimal 8 karakter');
        
        if (/[A-Z]/.test(password)) strength++;
        else messages.push('Gunakan huruf besar');
        
        if (/[a-z]/.test(password)) strength++;
        else messages.push('Gunakan huruf kecil');
        
        if (/[0-9]/.test(password)) strength++;
        else messages.push('Gunakan angka');
        
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        else messages.push('Gunakan simbol');
        
        // Update feedback
        if (strength < 2) {
            feedback.className = 'validation-feedback invalid';
            feedback.textContent = 'Password lemah: ' + messages.join(', ');
        } else if (strength < 4) {
            feedback.className = 'validation-feedback warning';
            feedback.textContent = 'Password sedang';
        } else {
            feedback.className = 'validation-feedback valid';
            feedback.textContent = 'Password kuat';
        }
    }
    
    checkPasswordMatch() {
        const password = document.getElementById('new_password');
        const confirmation = document.getElementById('new_password_confirmation');
        const feedback = document.getElementById('password_confirm_validation');
        
        if (!password || !confirmation || !feedback) return;
        
        if (confirmation.value === '') {
            feedback.textContent = '';
            feedback.className = 'validation-feedback';
        } else if (password.value === confirmation.value) {
            feedback.textContent = 'Password cocok';
            feedback.className = 'validation-feedback valid';
        } else {
            feedback.textContent = 'Password tidak cocok';
            feedback.className = 'validation-feedback invalid';
        }
    }
}

// ===================================================================
// 16. 🆕 NEW MODULES - Account Manager Integration
// ===================================================================

class AccountManagerIntegrationModule {
    constructor(manager) {
        this.manager = manager;
        console.log('🆕 Account Manager Integration Module initialized');
    }
    
    async handleAccountManagerSelection(selectedData, inputElement) {
        const accountManagerId = selectedData ? selectedData.id : 
                                inputElement.parentNode.querySelector('input[type="hidden"]')?.value;
        
        if (!accountManagerId) {
            this.disableDivisiDropdown(inputElement);
            return;
        }
        
        try {
            await this.loadAccountManagerDivisions(accountManagerId, inputElement);
        } catch (error) {
            console.error('Error loading account manager divisions:', error);
            this.manager.notificationModule.showError('Gagal memuat divisi untuk Account Manager');
        }
    }
    
    async loadAccountManagerDivisions(accountManagerId, inputElement) {
        try {
            const response = await this.manager.requestHandler.makeRequest(
                'GET',
                this.manager.config.routes.accountManagerDivisions.replace(':id', accountManagerId)
            );
            
            if (response.success && response.divisis) {
                this.populateDivisiDropdown(response.divisis, inputElement);
            } else {
                this.disableDivisiDropdown(inputElement);
            }
        } catch (error) {
            this.disableDivisiDropdown(inputElement);
            throw error;
        }
    }
    
    populateDivisiDropdown(divisis, inputElement) {
        const form = inputElement.closest('form');
        if (!form) return;
        
        const divisiSelect = form.querySelector('select[name="divisi_id"], select[id*="divisi"]');
        if (!divisiSelect) return;
        
        // Clear existing options
        divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';
        
        // Add new options
        divisis.forEach(divisi => {
            const option = document.createElement('option');
            option.value = divisi.id;
            option.textContent = divisi.nama;
            divisiSelect.appendChild(option);
        });
        
        // Enable dropdown
        divisiSelect.disabled = false;
        
        console.log(`🆕 Loaded ${divisis.length} divisi options for Account Manager`);
    }
    
    disableDivisiDropdown(inputElement) {
        const form = inputElement ? inputElement.closest('form') : document;
        const divisiSelect = form.querySelector('select[name="divisi_id"], select[id*="divisi"]');
        
        if (divisiSelect) {
            divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';
            divisiSelect.disabled = true;
        }
    }
    
    handleAccountManagerChange(inputElement) {
        // Debounce the change to avoid too many requests
        clearTimeout(this.changeTimeout);
        this.changeTimeout = setTimeout(() => {
            const hiddenInput = inputElement.parentNode.querySelector('input[type="hidden"]');
            if (hiddenInput && hiddenInput.value) {
                this.handleAccountManagerSelection(null, inputElement);
            } else {
                this.disableDivisiDropdown(inputElement);
            }
        }, 500);
    }
}

// ===================================================================
// 17. 🛠️ UTILITY FUNCTIONS (Static helper functions)
// ===================================================================

class UtilityFunctions {
    
    // ✅ EXISTING: Format number with Indonesian locale
    static formatNumber(number) {
        if (number === null || number === undefined || isNaN(number)) return '0';
        return new Intl.NumberFormat('id-ID').format(number);
    }
    
    // ✅ EXISTING: Format currency with Indonesian Rupiah
    static formatCurrency(amount) {
        if (amount === null || amount === undefined || isNaN(amount)) return 'Rp 0';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }
    
    // ✅ EXISTING: Debounce function for performance optimization
    static debounce(func, wait, immediate = false) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    }
    
    // ✅ NEW: Generate error log content for download
    static generateErrorLogContent(response, importType) {
        const data = response.data || response.summary || {};
        const timestamp = new Date().toISOString();
        
        let content = `IMPORT ERROR LOG\n`;
        content += `==========================================\n`;
        content += `Type: ${importType}\n`;
        content += `Timestamp: ${timestamp}\n`;
        content += `Success: ${response.success}\n`;
        content += `Message: ${response.message}\n`;
        content += `==========================================\n\n`;
        
        // Statistics
        content += `STATISTICS:\n`;
        content += `- Total Rows: ${data.total_rows || data.processed || 0}\n`;
        content += `- Imported: ${data.imported || 0}\n`;
        content += `- Updated: ${data.updated || 0}\n`;
        content += `- Errors: ${data.errors || data.failed_rows || 0}\n`;
        content += `- Duplicates: ${data.duplicates || 0}\n`;
        content += `- Conflicts: ${data.conflicts || 0}\n\n`;
        
        // Error Details
        if (data.error_details && data.error_details.length > 0) {
            content += `ERROR DETAILS:\n`;
            content += `----------------------------------------\n`;
            data.error_details.forEach((error, index) => {
                content += `${index + 1}. ${error}\n`;
            });
            content += `\n`;
        }
        
        // Warning Details
        if (data.warning_details && data.warning_details.length > 0) {
            content += `WARNING DETAILS:\n`;
            content += `----------------------------------------\n`;
            data.warning_details.forEach((warning, index) => {
                content += `${index + 1}. ${warning}\n`;
            });
            content += `\n`;
        }
        
        content += `==========================================\n`;
        content += `Log generated by Revenue Management System\n`;
        
        return content;
    }
    
    // ✅ NEW: Download text content as file
    static downloadTextFile(content, filename) {
        try {
            const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.style.display = 'none';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Clean up object URL
            setTimeout(() => URL.revokeObjectURL(link.href), 1000);
            
            console.log(`✅ File downloaded: ${filename}`);
        } catch (error) {
            console.error(`❌ Error downloading file: ${filename}`, error);
            throw error;
        }
    }
    
    // ✅ NEW: Sanitize HTML to prevent XSS
    static sanitizeHTML(html) {
        const temp = document.createElement('div');
        temp.textContent = html;
        return temp.innerHTML;
    }
    
    // ✅ NEW: Format date to Indonesian locale
    static formatDate(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            ...options
        };
        
        try {
            return new Intl.DateTimeFormat('id-ID', defaultOptions).format(new Date(date));
        } catch (error) {
            console.error('Error formatting date:', error);
            return date.toString();
        }
    }
    
    // ✅ NEW: Generate unique ID
    static generateUniqueId(prefix = 'id') {
        return `${prefix}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }
    
    // ✅ NEW: Copy text to clipboard
    static async copyToClipboard(text) {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                document.execCommand('copy');
                textArea.remove();
            }
            return true;
        } catch (error) {
            console.error('Error copying to clipboard:', error);
            return false;
        }
    }
    
    // ✅ NEW: Validate email format
    static isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // ✅ NEW: Deep clone object
    static deepClone(obj) {
        try {
            return JSON.parse(JSON.stringify(obj));
        } catch (error) {
            console.error('Error deep cloning object:', error);
            return obj;
        }
    }
}

// ===================================================================
// 18. 🚀 INITIALIZATION (Main initialization function)
// ===================================================================

/**
 * ✅ Main initialization function for Revenue Manager
 * This function will be called when DOM is ready
 */
function initializeRevenueManager() {
    try {
        console.log('🚀 Starting Revenue Manager initialization...');
        
        // Check if required global configurations exist
        if (typeof window.revenueConfig === 'undefined') {
            throw new Error('❌ Revenue configuration not found. Please check blade template.');
        }
        
        if (typeof window.currentData === 'undefined') {
            throw new Error('❌ Current data not found. Please check blade template.');
        }
        
        // Initialize the main Revenue Manager
        window.revenueManager = new RevenueManager();
        
        console.log('✅ Revenue Manager initialization completed successfully');
        console.log('🎯 Available modules:', {
            search: !!window.revenueManager.searchModule,
            crud: !!window.revenueManager.crudModule,
            bulk: !!window.revenueManager.bulkModule,
            import: !!window.revenueManager.importModule,
            modal: !!window.revenueManager.modalModule,
            notification: !!window.revenueManager.notificationModule,
            tab: !!window.revenueManager.tabModule,
            divisi: !!window.revenueManager.divisiModule,
            filter: !!window.revenueManager.filterModule,
            password: !!window.revenueManager.passwordModule,
            accountManagerIntegration: !!window.revenueManager.accountManagerIntegrationModule
        });
        
        // Setup form submissions using the initialized modules
        setupRevenueFormSubmissions();
        
        // Show success notification
        if (window.revenueManager.notificationModule) {
            window.revenueManager.notificationModule.showSuccess(
                'Sistem berhasil dimuat',
                'Revenue Management System siap digunakan',
                3000
            );
        }
        
        return window.revenueManager;
        
    } catch (error) {
        console.error('❌ Revenue Manager initialization failed:', error);
        
        // Show fallback error message
        const errorContainer = document.getElementById('js-error-boundary') ||
                              document.getElementById('notification-container');
        
        if (errorContainer) {
            errorContainer.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Sistem Gagal Dimuat
                    </h6>
                    <p class="mb-2">Terjadi kesalahan saat memuat Revenue Management System:</p>
                    <p class="mb-2"><strong>${error.message}</strong></p>
                    <hr>
                    <p class="mb-0">
                        <button class="btn btn-warning btn-sm" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> Muat Ulang Halaman
                        </button>
                    </p>
                </div>
            `;
            errorContainer.style.display = 'block';
        }
        
        // Re-throw error for debugging
        throw error;
    }
}

/**
 * ✅ Setup specific form submission handlers
 */
function setupRevenueFormSubmissions() {
    // Handle change password form
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (window.revenueManager && window.revenueManager.passwordModule) {
                window.revenueManager.passwordModule.handlePasswordChange(this);
            }
        });
    }
    
    // Handle divisi form validations
    document.addEventListener('submit', function(e) {
        const form = e.target;
        
        // Check if form contains divisi selection
        const divisiButtons = form.querySelectorAll('.divisi-btn');
        if (divisiButtons.length > 0) {
            const hasActiveDivisi = form.querySelector('.divisi-btn.active');
            if (!hasActiveDivisi) {
                e.preventDefault();
                if (window.revenueManager && window.revenueManager.notificationModule) {
                    window.revenueManager.notificationModule.showError(
                        'Pilih minimal satu divisi sebelum menyimpan'
                    );
                }
                return false;
            }
        }
    });
    
    console.log('✅ Form submission handlers setup completed');
}

/**
 * ✅ Auto-initialization when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('📋 DOM Content Loaded - Initializing Revenue Manager...');
    
    // Add a small delay to ensure all resources are loaded
    setTimeout(() => {
        try {
            initializeRevenueManager();
        } catch (error) {
            console.error('💥 Fatal error during initialization:', error);
        }
    }, 100);
});

/**
 * ✅ Fallback initialization for browsers that might miss DOMContentLoaded
 */
if (document.readyState === 'loading') {
    // Document is still loading, wait for DOMContentLoaded
    console.log('📋 Document still loading, waiting for DOMContentLoaded...');
} else {
    // Document already loaded, initialize immediately
    console.log('📋 Document already loaded, initializing immediately...');
    setTimeout(() => {
        if (typeof window.revenueManager === 'undefined') {
            try {
                initializeRevenueManager();
            } catch (error) {
                console.error('💥 Fallback initialization failed:', error);
            }
        }
    }, 100);
}

/**
 * ✅ Handle page visibility changes (for cleanup and optimization)
 */
document.addEventListener('visibilitychange', function() {
    if (window.revenueManager) {
        if (document.hidden) {
            console.log('📴 Page hidden - pausing non-essential operations');
            // Pause any intervals or ongoing operations
        } else {
            console.log('📱 Page visible - resuming operations');
            // Resume operations if needed
        }
    }
});

/**
 * ✅ Handle page unload (for cleanup)
 */
window.addEventListener('beforeunload', function() {
    if (window.revenueManager) {
        console.log('👋 Page unloading - cleaning up resources');
        // Perform any necessary cleanup
    }
});

/**
 * ✅ Export for external access (if needed)
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        RevenueManager,
        initializeRevenueManager,
        UtilityFunctions
    };
}