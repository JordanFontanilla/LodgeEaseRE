{{-- Notification Usage Examples --}}
@extends('layouts.admin')

@section('title', 'Notification Demo')
@section('page-title', 'Notification System Demo')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Notification System Demo</h2>
        <p class="text-gray-600 mb-6">Test the notification system with different types and options.</p>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <button id="successBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded transition-colors">
                Success
            </button>
            <button id="errorBtn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded transition-colors">
                Error
            </button>
            <button id="warningBtn" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded transition-colors">
                Warning
            </button>
            <button id="infoBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition-colors">
                Info
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button id="persistentBtn" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded transition-colors">
                Persistent Notification
            </button>
            <button id="quickBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition-colors">
                Quick Notification
            </button>
            <button id="htmlBtn" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded transition-colors">
                HTML Content
            </button>
            <button id="clearBtn" class="bg-red-400 hover:bg-red-500 text-white px-4 py-2 rounded transition-colors">
                Clear All
            </button>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Usage Guide</h3>
        
        <div class="space-y-4 text-sm text-gray-600">
            <div>
                <h4 class="font-medium text-gray-800 mb-2">Basic Usage:</h4>
                <div class="bg-gray-100 p-3 rounded font-mono">
                    // Show a notification<br>
                    window.notificationService.success('Title', 'Message');<br><br>
                    
                    // Or use the show method<br>
                    window.notificationService.show('success', 'Title', 'Message');
                </div>
            </div>
            
            <div>
                <h4 class="font-medium text-gray-800 mb-2">Available Methods:</h4>
                <div class="bg-gray-100 p-3 rounded font-mono">
                    notificationService.success(title, message, options)<br>
                    notificationService.error(title, message, options)<br>
                    notificationService.warning(title, message, options)<br>
                    notificationService.info(title, message, options)<br>
                    notificationService.notify(type, message, options)<br>
                    notificationService.hideAll()
                </div>
            </div>
            
            <div>
                <h4 class="font-medium text-gray-800 mb-2">Options:</h4>
                <div class="bg-gray-100 p-3 rounded font-mono">
                    {<br>
                    &nbsp;&nbsp;duration: 5000, // Auto-dismiss time in ms<br>
                    &nbsp;&nbsp;persistent: false, // Don't auto-dismiss<br>
                    &nbsp;&nbsp;allowHtml: false, // Allow HTML content<br>
                    &nbsp;&nbsp;onClick: function() { /* callback */ }<br>
                    }
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Success notification
    document.getElementById('successBtn').addEventListener('click', function() {
        window.notificationService.success('Success!', 'This is a success notification');
    });
    
    // Error notification
    document.getElementById('errorBtn').addEventListener('click', function() {
        window.notificationService.error('Error!', 'Something went wrong');
    });
    
    // Warning notification
    document.getElementById('warningBtn').addEventListener('click', function() {
        window.notificationService.warning('Warning!', 'Please check your input');
    });
    
    // Info notification
    document.getElementById('infoBtn').addEventListener('click', function() {
        window.notificationService.info('Information', 'Here is some useful information');
    });
    
    // Persistent notification
    document.getElementById('persistentBtn').addEventListener('click', function() {
        window.notificationService.show('info', 'Persistent', 'This notification will not auto-dismiss', {
            persistent: true
        });
    });
    
    // Quick notification (no title)
    document.getElementById('quickBtn').addEventListener('click', function() {
        window.notificationService.notify('success', 'Quick notification without title');
    });
    
    // HTML content
    document.getElementById('htmlBtn').addEventListener('click', function() {
        window.notificationService.show('info', 'HTML Content', '<strong>Bold text</strong> and <em>italic text</em>', {
            allowHtml: true,
            duration: 7000
        });
    });
    
    // Clear all notifications
    document.getElementById('clearBtn').addEventListener('click', function() {
        window.notificationService.hideAll();
    });
});
</script>
@endsection
