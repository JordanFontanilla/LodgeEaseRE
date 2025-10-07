// ChatBot Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeChatBot();
});

function initializeChatBot() {
    setupChatInput();
    setupQuickQuestions();
    setupKeyboardShortcuts();
    scrollToBottom();
}

function setupChatInput() {
    const chatInput = document.getElementById('chatInput');
    const charCount = document.getElementById('charCount');
    const sendButton = document.getElementById('sendButton');
    
    // Character count update
    chatInput.addEventListener('input', function() {
        const currentLength = this.value.length;
        charCount.textContent = `${currentLength}/1000`;
        
        // Update button state
        sendButton.disabled = currentLength === 0;
        
        // Color coding for character count
        if (currentLength > 900) {
            charCount.classList.add('text-red-500');
            charCount.classList.remove('text-gray-400', 'text-yellow-500');
        } else if (currentLength > 750) {
            charCount.classList.add('text-yellow-500');
            charCount.classList.remove('text-gray-400', 'text-red-500');
        } else {
            charCount.classList.add('text-gray-400');
            charCount.classList.remove('text-red-500', 'text-yellow-500');
        }
    });
    
    // Auto-resize input and handle Enter key
    chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(e);
        }
    });
    
    // Initial state
    sendButton.disabled = true;
}

function setupQuickQuestions() {
    const quickButtons = document.querySelectorAll('.quick-question-btn');
    
    quickButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Add visual feedback
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });
}

function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Focus on input with Ctrl/Cmd + K
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('chatInput').focus();
        }
        
        // New conversation with Ctrl/Cmd + N
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            startNewConversation();
        }
        
        // Toggle history with Ctrl/Cmd + H
        if ((e.ctrlKey || e.metaKey) && e.key === 'h') {
            e.preventDefault();
            toggleChatHistory();
        }
    });
}

function sendMessage(event) {
    event.preventDefault();
    
    const chatInput = document.getElementById('chatInput');
    const message = chatInput.value.trim();
    
    if (!message) return;
    
    // Add user message to chat
    addMessageToChat('user', message);
    
    // Clear input
    chatInput.value = '';
    document.getElementById('charCount').textContent = '0/1000';
    document.getElementById('sendButton').disabled = true;
    
    // Show loading indicator
    showLoadingIndicator();
    
    // Send message to server
    fetch('/admin/chatbot/message', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingIndicator();
        
        if (data.success) {
            // Add AI response to chat
            setTimeout(() => {
                addMessageToChat('assistant', data.response);
            }, 500); // Small delay for better UX
        } else {
            addMessageToChat('assistant', 'Sorry, I encountered an error. Please try again.');
        }
    })
    .catch(error => {
        hideLoadingIndicator();
        console.error('Error:', error);
        addMessageToChat('assistant', 'Sorry, I encountered a connection error. Please check your internet connection and try again.');
    });
}

function sendPredefinedQuestion(question) {
    const chatInput = document.getElementById('chatInput');
    chatInput.value = question;
    
    // Trigger character count update
    chatInput.dispatchEvent(new Event('input'));
    
    // Send the message
    const event = new Event('submit');
    sendMessage(event);
}

function addMessageToChat(type, content) {
    const chatMessages = document.getElementById('chatMessages');
    const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'flex items-start space-x-3 mb-6 animate-fade-in';
    
    if (type === 'user') {
        messageDiv.innerHTML = `
            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="bg-blue-600 text-white rounded-lg p-4">
                    <p>${content}</p>
                    <p class="text-blue-100 text-xs mt-2">${timestamp}</p>
                </div>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 003.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 00-3.09 3.091z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="bg-gray-100 rounded-lg p-4">
                    <p class="text-gray-900">${content}</p>
                    <p class="text-gray-500 text-xs mt-2">${timestamp}</p>
                </div>
            </div>
        `;
    }
    
    chatMessages.appendChild(messageDiv);
    scrollToBottom();
}

function showLoadingIndicator() {
    const chatMessages = document.getElementById('chatMessages');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadingClone = loadingIndicator.cloneNode(true);
    
    loadingClone.id = 'activeLoading';
    loadingClone.classList.remove('hidden');
    chatMessages.appendChild(loadingClone);
    
    scrollToBottom();
}

function hideLoadingIndicator() {
    const activeLoading = document.getElementById('activeLoading');
    if (activeLoading) {
        activeLoading.remove();
    }
}

function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

async function startNewConversation() {
    const chatMessages = document.getElementById('chatMessages');
    
    // Show confirmation dialog
    const confirmed = await window.notificationService?.confirm(
        'Start New Conversation', 
        'Start a new conversation? This will clear the current chat.',
        { 
            confirmText: 'Start New',
            type: 'warning'
        }
    ) ?? confirm('Start a new conversation? This will clear the current chat.');
    
    if (confirmed) {
        // Clear chat messages except welcome message
        const messages = chatMessages.querySelectorAll('.animate-fade-in');
        messages.forEach(message => {
            message.remove();
        });
        
        // Send request to server
        fetch('/admin/chatbot/new-conversation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('New conversation started!', 'success');
                
                // Focus on input
                setTimeout(() => {
                    document.getElementById('chatInput').focus();
                }, 500);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error starting new conversation', 'error');
        });
    }
}

function toggleChatHistory() {
    const modal = document.getElementById('chatHistoryModal');
    const isHidden = modal.classList.contains('hidden');
    
    if (isHidden) {
        loadChatHistory();
        modal.classList.remove('hidden');
        
        // Add fade-in animation
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);
    } else {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

function loadChatHistory() {
    const historyList = document.getElementById('historyList');
    historyList.innerHTML = '<div class="text-center text-gray-500">Loading...</div>';
    
    fetch('/admin/chatbot/history')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderChatHistory(data.history);
            } else {
                historyList.innerHTML = '<div class="text-center text-red-500">Error loading history</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            historyList.innerHTML = '<div class="text-center text-red-500">Connection error</div>';
        });
}

function renderChatHistory(history) {
    const historyList = document.getElementById('historyList');
    
    if (history.length === 0) {
        historyList.innerHTML = '<div class="text-center text-gray-500">No chat history found</div>';
        return;
    }
    
    historyList.innerHTML = history.map(item => `
        <div class="p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
             onclick="loadConversation(${item.id})">
            <h4 class="font-medium text-gray-900 mb-1">${item.title}</h4>
            <p class="text-sm text-gray-600 mb-2">${item.last_message}</p>
            <p class="text-xs text-gray-400">${formatDate(item.timestamp)}</p>
        </div>
    `).join('');
}

function loadConversation(conversationId) {
    // Implementation for loading specific conversation
    console.log('Loading conversation:', conversationId);
    toggleChatHistory();
    showNotification('Loading conversation...', 'info');
}

function formatDate(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
    
    switch(type) {
        case 'success':
            notification.classList.add('bg-green-100', 'text-green-800', 'border', 'border-green-200');
            break;
        case 'error':
            notification.classList.add('bg-red-100', 'text-red-800', 'border', 'border-red-200');
            break;
        default:
            notification.classList.add('bg-blue-100', 'text-blue-800', 'border', 'border-blue-200');
    }
    
    notification.innerHTML = `
        <div class="flex items-center space-x-2">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-current opacity-70 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Export functions for global use
window.sendPredefinedQuestion = sendPredefinedQuestion;
window.startNewConversation = startNewConversation;
window.toggleChatHistory = toggleChatHistory;
window.loadConversation = loadConversation;
