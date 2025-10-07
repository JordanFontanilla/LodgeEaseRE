/**
 * Activity Log Firebase Integration Fix
 * This file contains fixed methods for the activity log functionality
 */

// Fix for renderLogDetails method
function renderLogDetailsFixed(log) {
    const categoryClass = getCategoryClassFixed(log.category);
    const severityClass = getSeverityClassFixed(log.severity);
    
    let html = '<div class="space-y-6">';
    
    // Header Information
    html += '<div class="bg-gray-50 rounded-lg p-4">';
    html += '<div class="grid grid-cols-1 md:grid-cols-3 gap-4">';
    
    // Timestamp
    html += '<div>';
    html += '<label class="block text-sm font-medium text-gray-700">Timestamp</label>';
    html += '<p class="mt-1 text-sm text-gray-900">' + (log.created_at || 'N/A') + '</p>';
    html += '<p class="text-xs text-gray-500">' + (log.created_at_human || '') + '</p>';
    html += '</div>';
    
    // Category
    html += '<div>';
    html += '<label class="block text-sm font-medium text-gray-700">Category</label>';
    html += '<span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' + categoryClass + '">';
    html += (log.category ? log.category.charAt(0).toUpperCase() + log.category.slice(1) : 'General');
    html += '</span>';
    html += '</div>';
    
    // Severity
    html += '<div>';
    html += '<label class="block text-sm font-medium text-gray-700">Severity</label>';
    html += '<span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' + severityClass + '">';
    html += (log.severity ? log.severity.charAt(0).toUpperCase() + log.severity.slice(1) : 'Low');
    html += '</span>';
    html += '</div>';
    
    html += '</div></div>';
    
    // Main Information
    html += '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    
    // User
    html += '<div>';
    html += '<label class="block text-sm font-medium text-gray-700">User</label>';
    html += '<p class="mt-1 text-sm text-gray-900">' + (log.admin_name || 'System') + '</p>';
    html += '<p class="text-xs text-gray-500">Module: ' + (log.module || 'system') + '</p>';
    html += '</div>';
    
    // Action
    html += '<div>';
    html += '<label class="block text-sm font-medium text-gray-700">Action</label>';
    html += '<p class="mt-1 text-sm text-gray-900">' + (log.action || 'Unknown') + '</p>';
    html += '</div>';
    
    html += '</div>';
    
    // Description
    html += '<div>';
    html += '<label class="block text-sm font-medium text-gray-700">Description</label>';
    html += '<div class="mt-1 p-3 bg-gray-50 rounded-lg">';
    html += '<p class="text-sm text-gray-900">' + (log.description || 'No description available') + '</p>';
    html += '</div>';
    html += '</div>';
    
    // Technical Details
    html += '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    
    // IP Address
    html += '<div>';
    html += '<label class="block text-sm font-medium text-gray-700">IP Address</label>';
    html += '<p class="mt-1 text-sm text-gray-900 font-mono">' + (log.ip_address || 'N/A') + '</p>';
    html += '</div>';
    
    // Log ID
    html += '<div>';
    html += '<label class="block text-sm font-medium text-gray-700">Log ID</label>';
    html += '<p class="mt-1 text-sm text-gray-900 font-mono">' + (log.id || 'N/A') + '</p>';
    html += '</div>';
    
    html += '</div>';
    
    // Metadata
    if (log.metadata && Object.keys(log.metadata).length > 0) {
        html += '<div>';
        html += '<label class="block text-sm font-medium text-gray-700 mb-2">Additional Metadata</label>';
        html += '<div class="bg-gray-50 rounded-lg p-3">';
        html += '<pre class="text-xs text-gray-700 whitespace-pre-wrap">' + JSON.stringify(log.metadata, null, 2) + '</pre>';
        html += '</div>';
        html += '</div>';
    }
    
    // User Agent
    if (log.user_agent && log.user_agent !== 'N/A') {
        html += '<div>';
        html += '<label class="block text-sm font-medium text-gray-700">User Agent</label>';
        html += '<div class="mt-1 p-3 bg-gray-50 rounded-lg">';
        html += '<p class="text-xs text-gray-700 break-all">' + log.user_agent + '</p>';
        html += '</div>';
        html += '</div>';
    }
    
    html += '</div>';
    
    return html;
}

function getCategoryClassFixed(category) {
    switch(category) {
        case 'auth': return 'bg-green-100 text-green-800';
        case 'room': return 'bg-blue-100 text-blue-800';
        case 'booking': return 'bg-purple-100 text-purple-800';
        case 'settings': return 'bg-yellow-100 text-yellow-800';
        case 'analytics': return 'bg-indigo-100 text-indigo-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getSeverityClassFixed(severity) {
    switch(severity) {
        case 'critical': return 'bg-red-100 text-red-800';
        case 'high': return 'bg-orange-100 text-orange-800';
        case 'medium': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-green-100 text-green-800';
    }
}

// Override the method if ActivityLog exists
if (typeof window.activityLog !== 'undefined') {
    window.activityLog.renderLogDetails = renderLogDetailsFixed;
}

// Global function for showing log details
window.showLogDetailsFixed = function(logId) {
    if (window.activityLog) {
        window.activityLog.showLogDetails(logId);
    }
};