@extends('layouts.admin')

@section('title', 'AI Assistant')
@section('page-title', 'AI Assistant')

@section('content')
    @component('components.loading-screen', [
        'id' => 'admin-loading',
        'message' => 'Loading...',
        'type' => 'admin',
        'overlay' => true,
        'showProgress' => false
    ])
    @endcomponent
    
    <!-- ChatBot Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">AI Assistant</h1>
                </div>
                <p class="text-gray-600">*AI Assistant may make mistakes, double check responses</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="startNewConversation()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Conversation
                </button>
                <button onclick="toggleChatHistory()" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Chat History
                </button>
            </div>
        </div>
    </div>

    <!-- Main Chat Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex h-[700px]">
        <!-- Chat Area -->
        <div class="flex-1 flex flex-col">
            <!-- Chat Messages -->
            <div id="chatMessages" class="flex-1 p-6 overflow-y-auto">
                <!-- Welcome Message -->
                <div class="flex items-start space-x-3 mb-6">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 003.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 00-3.09 3.091z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="bg-gray-100 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900 mb-2">Welcome to Lodge Ease AI Assistant!</h3>
                            <p class="text-gray-700 mb-3">I can help you analyze your hotel data and provide valuable insights to improve your business. Here are some areas I can assist you with:</p>
                            <ul class="text-gray-700 space-y-1 ml-4">
                                <li>• Occupancy trends and room statistics</li>
                                <li>• Sales and financial performance</li>
                                <li>• Booking patterns and guest preferences</li>
                                <li>• Overall business performance</li>
                            </ul>
                            <p class="text-gray-700 mt-3">How can I assist you today?</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suggested Questions -->
            <div class="border-t border-gray-200 p-4">
                <h3 class="font-medium text-gray-900 mb-3">Quick Questions:</h3>
                
                <!-- All Questions in a compact grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Occupancy Questions -->
                    <div>
                        <h4 class="text-xs font-medium text-gray-700 mb-2">Occupancy</h4>
                        <div class="space-y-1">
                            @foreach($questionCategories['Occupancy'] as $question)
                            <button onclick="sendPredefinedQuestion('{{ $question }}')" 
                                    class="quick-question-btn w-full text-left px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded hover:bg-blue-100 transition-colors">
                                {{ $question }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Sales Questions -->
                    <div>
                        <h4 class="text-xs font-medium text-gray-700 mb-2">Sales</h4>
                        <div class="space-y-1">
                            @foreach($questionCategories['Sales'] as $question)
                            <button onclick="sendPredefinedQuestion('{{ $question }}')" 
                                    class="quick-question-btn w-full text-left px-2 py-1 bg-green-50 text-green-700 text-xs rounded hover:bg-green-100 transition-colors">
                                {{ $question }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Bookings Questions -->
                    <div>
                        <h4 class="text-xs font-medium text-gray-700 mb-2">Bookings</h4>
                        <div class="space-y-1">
                            @foreach($questionCategories['Bookings'] as $question)
                            <button onclick="sendPredefinedQuestion('{{ $question }}')" 
                                    class="quick-question-btn w-full text-left px-2 py-1 bg-purple-50 text-purple-700 text-xs rounded hover:bg-purple-100 transition-colors">
                                {{ $question }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Performance Questions -->
                    <div>
                        <h4 class="text-xs font-medium text-gray-700 mb-2">Performance</h4>
                        <div class="space-y-1">
                            @foreach($questionCategories['Performance'] as $question)
                            <button onclick="sendPredefinedQuestion('{{ $question }}')" 
                                    class="quick-question-btn w-full text-left px-2 py-1 bg-orange-50 text-orange-700 text-xs rounded hover:bg-orange-100 transition-colors">
                                {{ $question }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Input -->
            <div class="border-t border-gray-200 p-4">
                <form onsubmit="sendMessage(event)" class="flex space-x-3">
                    <div class="flex-1 relative">
                        <input type="text" 
                               id="chatInput" 
                               placeholder="Ask about hotel forecasts..." 
                               class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                               maxlength="1000">
                        <div class="absolute inset-y-0 right-3 flex items-center">
                            <span class="text-xs text-gray-400" id="charCount">0/1000</span>
                        </div>
                    </div>
                    <button type="submit" 
                            id="sendButton"
                            class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

{{-- Include Chatbot Modals --}}
@include('components.modals_admin', ['type' => 'chatbot'])

@section('scripts')
    @vite(['resources/js/chatbot.js'])
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show loading screen when page loads
        window.LoadingScreen.show({
            id: 'admin-loading',
            message: 'Loading AI Assistant...'
        });
        
        // Simulate AI assistant initialization
        setTimeout(() => {
            window.LoadingScreen.updateMessage('admin-loading', 'Connecting to AI services...');
        }, 500);
        
        setTimeout(() => {
            window.LoadingScreen.updateMessage('admin-loading', 'Loading chat history...');
        }, 1000);
        
        // Hide loading screen once chatbot is ready
        setTimeout(() => {
            window.LoadingScreen.hide('admin-loading');
        }, 1500);
        
        // Add loading to new conversation button
        const newConversationBtn = document.querySelector('button[onclick*="startNewConversation"]');
        if (newConversationBtn) {
            newConversationBtn.addEventListener('click', function() {
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: 'Starting new conversation...',
                    timeout: 2000
                });
            });
        }
        
        // Add loading to chat history button
        const chatHistoryBtn = document.querySelector('button[onclick*="toggleChatHistory"]');
        if (chatHistoryBtn) {
            chatHistoryBtn.addEventListener('click', function() {
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: 'Loading chat history...',
                    timeout: 2000
                });
            });
        }
        
        // Add loading to message sending
        const messageForm = document.querySelector('#messageForm');
        if (messageForm) {
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const messageInput = this.querySelector('input[type="text"]');
                const message = messageInput?.value.trim();
                
                if (message) {
                    window.LoadingScreen.show({
                        id: 'admin-loading',
                        message: 'Sending message...',
                        showProgress: true
                    });
                    
                    // Simulate message sending and AI response
                    let progress = 0;
                    const sendInterval = setInterval(() => {
                        progress += Math.random() * 25;
                        
                        if (progress <= 30) {
                            window.LoadingScreen.updateMessage('admin-loading', 'Processing your message...');
                        } else if (progress <= 60) {
                            window.LoadingScreen.updateMessage('admin-loading', 'Getting AI response...');
                        } else if (progress <= 90) {
                            window.LoadingScreen.updateMessage('admin-loading', 'Generating response...');
                        }
                        
                        window.LoadingScreen.updateProgress('admin-loading', Math.min(progress, 100));
                        
                        if (progress >= 100) {
                            clearInterval(sendInterval);
                            window.LoadingScreen.updateMessage('admin-loading', 'Message sent!');
                            
                            setTimeout(() => {
                                window.LoadingScreen.hide('admin-loading');
                                // Submit the actual form or handle message sending here
                                if (messageInput) messageInput.value = '';
                            }, 1000);
                        }
                    }, 200);
                }
            });
        }
        
        // Add loading to attach file button
        const attachBtn = document.querySelector('button[type="button"]');
        if (attachBtn && attachBtn.innerHTML.includes('paperclip')) {
            attachBtn.addEventListener('click', function() {
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: 'Opening file selector...',
                    timeout: 1500
                });
            });
        }
        
        // Add loading to conversation items (if they exist in chat history)
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', function() {
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: 'Loading conversation...',
                    timeout: 2000
                });
            });
        });
        
        // Add loading to clear chat button (if it exists)
        const clearChatBtn = document.querySelector('button[onclick*="clear"]');
        if (clearChatBtn) {
            clearChatBtn.addEventListener('click', function() {
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: 'Clearing chat history...',
                    timeout: 2000
                });
            });
        }
        
        // Add loading to export/download chat buttons (if they exist)
        document.querySelectorAll('button[onclick*="export"], button[onclick*="download"]').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.textContent.trim().toLowerCase();
                let message = 'Processing...';
                
                if (action.includes('export')) {
                    message = 'Exporting chat history...';
                } else if (action.includes('download')) {
                    message = 'Downloading chat...';
                }
                
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: message,
                    showProgress: true
                });
            });
        });
        
        // Add loading to settings/configuration buttons (if they exist)
        document.querySelectorAll('button[onclick*="settings"], button[onclick*="config"]').forEach(button => {
            button.addEventListener('click', function() {
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: 'Loading AI settings...',
                    timeout: 2000
                });
            });
        });
        
        // Add loading to regenerate response buttons (if they exist)
        document.querySelectorAll('button[onclick*="regenerate"], .regenerate-btn').forEach(button => {
            button.addEventListener('click', function() {
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: 'Regenerating response...',
                    showProgress: true
                });
            });
        });
        
        // Add loading to navigation links
        document.querySelectorAll('.dashboard-nav-item').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!this.classList.contains('active')) {
                    const targetName = this.textContent.trim();
                    window.LoadingScreen.show({
                        id: 'admin-loading',
                        message: `Loading ${targetName}...`,
                        timeout: 10000
                    });
                }
            });
        });
        
        // Add loading to file uploads (if file input is added dynamically)
        document.addEventListener('change', function(e) {
            if (e.target.type === 'file' && e.target.files.length > 0) {
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: 'Uploading file...',
                    showProgress: true
                });
                
                // Simulate file upload progress
                let uploadProgress = 0;
                const uploadInterval = setInterval(() => {
                    uploadProgress += Math.random() * 15;
                    window.LoadingScreen.updateProgress('admin-loading', Math.min(uploadProgress, 100));
                    
                    if (uploadProgress >= 100) {
                        clearInterval(uploadInterval);
                        window.LoadingScreen.updateMessage('admin-loading', 'File uploaded successfully!');
                        setTimeout(() => {
                            window.LoadingScreen.hide('admin-loading');
                        }, 1000);
                    }
                }, 300);
            }
        });
    });
    </script>
@endsection
