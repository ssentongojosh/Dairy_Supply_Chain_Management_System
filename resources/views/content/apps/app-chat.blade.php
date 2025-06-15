@extends('layouts/contentNavbarLayout')

@section('title', 'Chat - DSCMS')

@section('page-style')
<style>
/* Chat Application Styles - Materio Design */
.app-chat {
  position: relative;
  height: calc(100vh - 160px);
  min-height: 600px;
  border-radius: 0.5rem;
  overflow: hidden;
}

.chat-sidebar {
  position: absolute;
  top: 0;
  left: 0;
  width: 370px;
  height: 100%;
  background: #fff;
  border-right: 1px solid var(--bs-border-color);
  z-index: 3;
  transition: transform 0.3s ease;
}

.chat-sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--bs-border-color);
}

.chat-sidebar-search {
  position: relative;
  margin-bottom: 1rem;
}

.chat-sidebar-search input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--bs-border-color);
  border-radius: 0.375rem;
  font-size: 0.875rem;
}

.chat-sidebar-search .search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #a8b1bb;
  font-size: 1.125rem;
}

.chat-contacts-header {
  padding: 1rem 1.5rem 0.5rem;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #a8b1bb;
  letter-spacing: 0.5px;
}

.chat-contacts-list {
  height: calc(100% - 200px);
  overflow-y: auto;
}

.chat-contact-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
  border: none;
  background: transparent;
  width: 100%;
  text-align: left;
}

.chat-contact-item:hover,
.chat-contact-item.active {
  background-color: rgba(105, 108, 255, 0.08);
}

.chat-contact-item.active {
  border-right: 3px solid #696cff;
}

.contact-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
  margin-right: 0.75rem;
  position: relative;
}

.contact-avatar.online::after {
  content: '';
  position: absolute;
  bottom: 0;
  right: 0;
  width: 10px;
  height: 10px;
  background: #28c76f;
  border: 2px solid #fff;
  border-radius: 50%;
}

.contact-info {
  flex: 1;
  overflow: hidden;
}

.contact-name {
  font-weight: 500;
  font-size: 0.875rem;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.contact-status {
  font-size: 0.75rem;
  color: #a8b1bb;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.contact-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.contact-time {
  font-size: 0.75rem;
  color: #a8b1bb;
  margin-bottom: 0.25rem;
}

.unread-badge {
  background: #696cff;
  color: #fff;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.625rem;
  font-weight: 600;
}

.chat-content {
  position: absolute;
  top: 0;
  left: 370px;
  right: 0;
  height: 100%;
  background: #fff;
  display: flex;
  flex-direction: column;
}

.chat-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1.5px solid var(--bs-border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.chat-header-info {
  display: flex;
  align-items: center;
}

.chat-header-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  margin-right: 0.875rem;
  position: relative;
}

.chat-header-avatar.online::after {
  content: '';
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  background: #28c76f;
  border: 2px solid #fff;
  border-radius: 50%;
}

.chat-header-details h6 {
  margin: 0;
  font-weight: 500;
  font-size: 1rem;
}

.chat-header-details small {
  color: #a8b1bb;
  font-size: 0.75rem;
}

.chat-header-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.chat-header-action {
  width: 36px;
  height: 36px;
  border: none;
  background: transparent;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #a8b1bb;
  cursor: pointer;
  transition: all 0.2s ease;
}

.chat-header-action:hover {
  background: rgba(105, 108, 255, 0.08);
  color: #696cff;
}

.chat-messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  background: #f8f9fa;
}

.chat-message {
  display: flex;
  margin-bottom: 1.5rem;
  align-items: flex-end;
}

.chat-message.sent {
  justify-content: flex-end;
}

.message-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.75rem;
  margin-right: 0.75rem;
  flex-shrink: 0;
}

.chat-message.sent .message-avatar {
  margin-right: 0;
  margin-left: 0.75rem;
  order: 2;
}

.message-content {
  max-width: 75%;
  position: relative;
}

.message-bubble {
  padding: 0.75rem 1rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  line-height: 1.4;
  position: relative;
  word-wrap: break-word;
}

.chat-message:not(.sent) .message-bubble {
  background: #fff;
  border: 1px solid var(--bs-border-color);
  border-bottom-left-radius: 0.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chat-message.sent .message-bubble {
  background: #696cff;
  color: #fff;
  border-bottom-right-radius: 0.25rem;
  margin-left: auto;
}

.message-time {
  font-size: 0.6875rem;
  color: #a8b1bb;
  margin-top: 0.25rem;
  text-align: right;
}

.chat-message.sent .message-time {
  color: rgba(255, 255, 255, 0.7);
}

.chat-input-container {
  padding: 1.25rem 1.5rem;
  border-top: 1px solid var(--bs-border-color);
  background: #fff;
}

.chat-input-wrapper {
  position: relative;
  display: flex;
  align-items: flex-end;
  gap: 0.75rem;
}

.chat-input {
  flex: 1;
  min-height: 42px;
  max-height: 120px;
  padding: 0.625rem 1rem 0.625rem 1rem;
  border: 1px solid var(--bs-border-color);
  border-radius: 1.5rem;
  resize: none;
  font-size: 0.875rem;
  line-height: 1.5;
  background: #f8f9fa;
  transition: all 0.2s ease;
}

.chat-input:focus {
  outline: none;
  border-color: #696cff;
  background: #fff;
  box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

.chat-input-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.chat-input-action {
  width: 40px;
  height: 40px;
  border: none;
  background: transparent;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #a8b1bb;
  cursor: pointer;
  transition: all 0.2s ease;
}

.chat-input-action:hover {
  background: rgba(105, 108, 255, 0.08);
  color: #696cff;
}

.send-btn {
  background: #696cff !important;
  color: #fff !important;
}

.send-btn:hover {
  background: #5f63ff !important;
  color: #fff !important;
}

.send-btn:disabled {
  background: #d4d7e1 !important;
  color: #a8b1bb !important;
  cursor: not-allowed;
}

.chat-welcome {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  text-align: center;
  color: #a8b1bb;
}

.chat-welcome-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.sidebar-toggle {
  display: none;
  position: absolute;
  top: 1rem;
  left: 1rem;
  z-index: 4;
  width: 40px;
  height: 40px;
  border: none;
  background: #fff;
  border-radius: 50%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  align-items: center;
  justify-content: center;
  color: #696cff;
  cursor: pointer;
}

/* Responsive Design */
@media (max-width: 1199.98px) {
  .chat-sidebar {
    width: 320px;
  }

  .chat-content {
    left: 320px;
  }
}

@media (max-width: 991.98px) {
  .chat-sidebar {
    transform: translateX(-100%);
    width: 100%;
    max-width: 370px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
  }

  .chat-sidebar.show {
    transform: translateX(0);
  }

  .chat-content {
    left: 0;
  }

  .sidebar-toggle {
    display: flex;
  }
}

@media (max-width: 575.98px) {
  .app-chat {
    height: calc(100vh - 120px);
  }

  .chat-header {
    padding: 1rem;
  }

  .chat-messages-container {
    padding: 1rem;
  }

  .chat-input-container {
    padding: 1rem;
  }

  .message-content {
    max-width: 85%;
  }
}

/* Color variants for avatars */
.bg-primary { background-color: #696cff !important; }
.bg-success { background-color: #28c76f !important; }
.bg-danger { background-color: #ea5455 !important; }
.bg-warning { background-color: #ff9f43 !important; }
.bg-info { background-color: #00bad1 !important; }
.bg-secondary { background-color: #82868b !important; }
</style>
@endsection

@section('content')
<div class="container-fluid">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Apps /</span> Chat
  </h4>

  <div class="card p-0">
    <div class="app-chat">
      <!-- Sidebar Toggle Button (Mobile) -->
      <button class="sidebar-toggle" id="sidebarToggle">
        <i class="ri-menu-line"></i>
      </button>

      <!-- Chat Sidebar -->
      <div class="chat-sidebar" id="chatSidebar">
        <div class="chat-sidebar-header">
          <div class="d-flex align-items-center mb-3">
            <div class="contact-avatar bg-primary me-3">
              {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div>
              <h6 class="mb-0">{{ Auth::user()->name }}</h6>
              <small class="text-success">Online</small>
            </div>
          </div>

          <div class="chat-sidebar-search">
            <i class="ri-search-line search-icon"></i>
            <input type="text" placeholder="Search contacts..." class="form-control">
          </div>
        </div>

        <div class="chat-contacts-header">
          Contacts
        </div>

        <div class="chat-contacts-list">
          <button class="chat-contact-item active" data-contact="support" data-name="DSCMS Support" data-status="Online">
            <div class="contact-avatar bg-primary online">DS</div>
            <div class="contact-info">
              <p class="contact-name">DSCMS Support</p>
              <p class="contact-status">Always here to help</p>
            </div>
            <div class="contact-meta">
              <span class="contact-time">Now</span>
              <span class="unread-badge">2</span>
            </div>
          </button>

          <button class="chat-contact-item" data-contact="team" data-name="Development Team" data-status="3 members online">
            <div class="contact-avatar bg-success online">DT</div>
            <div class="contact-info">
              <p class="contact-name">Development Team</p>
              <p class="contact-status">Working on new features</p>
            </div>
            <div class="contact-meta">
              <span class="contact-time">5m</span>
            </div>
          </button>

          <button class="chat-contact-item" data-contact="admin" data-name="Admin Panel" data-status="Last seen 1h ago">
            <div class="contact-avatar bg-warning">AP</div>
            <div class="contact-info">
              <p class="contact-name">Admin Panel</p>
              <p class="contact-status">System notifications</p>
            </div>
            <div class="contact-meta">
              <span class="contact-time">1h</span>
            </div>
          </button>

          <button class="chat-contact-item" data-contact="security" data-name="Security Team" data-status="Online">
            <div class="contact-avatar bg-danger online">ST</div>
            <div class="contact-info">
              <p class="contact-name">Security Team</p>
              <p class="contact-status">Monitoring systems</p>
            </div>
            <div class="contact-meta">
              <span class="contact-time">2h</span>
            </div>
          </button>

          <button class="chat-contact-item" data-contact="general" data-name="General Discussion" data-status="12 members">
            <div class="contact-avatar bg-info">GD</div>
            <div class="contact-info">
              <p class="contact-name">General Discussion</p>
              <p class="contact-status">Share ideas and feedback</p>
            </div>
            <div class="contact-meta">
              <span class="contact-time">4h</span>
            </div>
          </button>
        </div>
      </div>

      <!-- Chat Content -->
      <div class="chat-content">
        <!-- Chat Header -->
        <div class="chat-header">
          <div class="chat-header-info">
            <div class="chat-header-avatar bg-primary online" id="chatHeaderAvatar">
              DS
            </div>
            <div class="chat-header-details">
              <h6 id="chatHeaderName">DSCMS Support</h6>
              <small id="chatHeaderStatus">Online</small>
            </div>
          </div>

          <div class="chat-header-actions">
            <button class="chat-header-action" title="Search">
              <i class="ri-search-line"></i>
            </button>
            <button class="chat-header-action" title="Video Call">
              <i class="ri-vidicon-line"></i>
            </button>
            <button class="chat-header-action" title="Phone Call">
              <i class="ri-phone-line"></i>
            </button>
            <button class="chat-header-action" title="More Options">
              <i class="ri-more-2-line"></i>
            </button>
          </div>
        </div>

        <!-- Chat Messages -->
        <div class="chat-messages-container" id="chatMessages">
          <div class="chat-welcome">
            <i class="ri-message-3-line chat-welcome-icon"></i>
            <h5>Welcome to DSCMS Chat!</h5>
            <p>Start a conversation with the DSCMS Support team.</p>
          </div>
        </div>

        <!-- Chat Input -->
        <div class="chat-input-container">
          <div class="chat-input-wrapper">
            <textarea
              class="chat-input"
              id="messageInput"
              placeholder="Type your message..."
              rows="1"
              maxlength="1000"
            ></textarea>
            <div class="chat-input-actions">
              <button class="chat-input-action" title="Attach File">
                <i class="ri-attachment-2"></i>
              </button>
              <button class="chat-input-action send-btn" id="sendButton" title="Send Message">
                <i class="ri-send-plane-2-fill"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DSCMS Chat App - DOM Content Loaded');

    // DOM Elements
    const chatMessages = document.getElementById('chatMessages');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const chatSidebar = document.getElementById('chatSidebar');
    const contactItems = document.querySelectorAll('.chat-contact-item');
    const chatHeaderName = document.getElementById('chatHeaderName');
    const chatHeaderStatus = document.getElementById('chatHeaderStatus');
    const chatHeaderAvatar = document.getElementById('chatHeaderAvatar');

    console.log('Elements initialized:', {
        chatMessages: !!chatMessages,
        messageInput: !!messageInput,
        sendButton: !!sendButton,
        contactItems: contactItems.length
    });

    // Check if all required elements exist
    if (!chatMessages || !messageInput || !sendButton) {
        console.error('Required DOM elements not found');
        return;
    }

    // State variables
    let messages = [];
    let currentContact = 'support';
    let currentContactName = 'DSCMS Support';

    // Auto-resize textarea
    if (messageInput) {
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    }

    // Sidebar toggle for mobile
    if (sidebarToggle && chatSidebar) {
        sidebarToggle.addEventListener('click', function() {
            chatSidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 991 &&
                !chatSidebar.contains(e.target) &&
                !sidebarToggle.contains(e.target) &&
                chatSidebar.classList.contains('show')) {
                chatSidebar.classList.remove('show');
            }
        });
    }

    // Contact switching
    contactItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all contacts
            contactItems.forEach(contact => contact.classList.remove('active'));

            // Add active class to clicked contact
            this.classList.add('active');

            // Get contact data
            const contactId = this.dataset.contact;
            const contactName = this.dataset.name;
            const contactStatus = this.dataset.status;

            // Update current contact
            currentContact = contactId;
            currentContactName = contactName;

            // Update header
            updateChatHeader(contactName, contactStatus);

            // Load messages for this contact
            loadContactMessages(contactId);

            // Close sidebar on mobile
            if (window.innerWidth <= 991 && chatSidebar) {
                chatSidebar.classList.remove('show');
            }

            // Remove unread badge
            const badge = this.querySelector('.unread-badge');
            if (badge) {
                badge.remove();
            }
        });
    });

    // Initialize chat
    loadMessages();

    // Send message events
    sendButton.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    function updateChatHeader(name, status) {
        if (chatHeaderName) chatHeaderName.textContent = name;
        if (chatHeaderStatus) chatHeaderStatus.textContent = status;

        // Update avatar with first letters of name
        if (chatHeaderAvatar) {
            const initials = name.split(' ').map(word => word.charAt(0)).join('').substring(0, 2);
            chatHeaderAvatar.textContent = initials;
        }
    }

    function loadContactMessages(contactId) {
        // Clear current messages
        messages = [];

        // Show welcome message for new contact
        if (contactId === 'support') {
            chatMessages.innerHTML = `
                <div class="chat-welcome">
                    <i class="ri-customer-service-2-line chat-welcome-icon"></i>
                    <h5>DSCMS Support</h5>
                    <p>Hi! I'm here to help you with any questions about the DSCMS platform.</p>
                </div>
            `;
        } else {
            chatMessages.innerHTML = `
                <div class="chat-welcome">
                    <i class="ri-message-3-line chat-welcome-icon"></i>
                    <h5>${currentContactName}</h5>
                    <p>Start a conversation with ${currentContactName}.</p>
                </div>
            `;
        }
    }

    function loadMessages() {
        fetch('{{ route('chat.messages') }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messages = data.messages;
                if (messages.length > 0) {
                    renderMessages();
                }
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
    }

    function sendMessage() {
        const message = messageInput.value.trim();
        if (!message) return;

        // Disable input while sending
        setInputState(false);

        fetch('{{ route('chat.send') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                contact: currentContact
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add message to local messages array
                const newMessage = {
                    id: Date.now(),
                    sender: data.data.sender,
                    message: data.data.message,
                    timestamp: data.data.timestamp,
                    is_own: true
                };

                messages.push(newMessage);
                renderMessages();
                messageInput.value = '';
                messageInput.style.height = 'auto';

                // Simulate a response after a delay
                setTimeout(() => {
                    simulateResponse();
                }, 1000 + Math.random() * 2000);
            } else {
                showNotification('Failed to send message. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            showNotification('Error sending message. Please check your connection.', 'error');
        })
        .finally(() => {
            setInputState(true);
        });
    }

    function simulateResponse() {
        const responses = {
            support: [
                "Thanks for reaching out! How can I assist you with DSCMS today?",
                "I've received your message and I'm here to help. What specific issue are you facing?",
                "That's a great question! Let me provide you with the information you need.",
                "I understand your concern. Here's what I recommend...",
                "Thanks for using DSCMS! Is there anything else I can help you with?",
                "I'm here 24/7 to support you with any DSCMS related questions."
            ],
            team: [
                "Hey! The development team is currently working on some exciting new features.",
                "Thanks for the feedback! We'll discuss this in our next sprint planning.",
                "Great idea! We've added this to our feature request backlog.",
                "The latest update includes the improvements you suggested.",
                "We're always looking for ways to improve DSCMS. What do you think about...?"
            ],
            admin: [
                "System status: All services are running normally.",
                "Your account permissions have been updated successfully.",
                "New security patches have been applied to the system.",
                "Scheduled maintenance will occur this weekend from 2-4 AM.",
                "Database backup completed successfully."
            ],
            security: [
                "Security team here. All systems are secure and monitored 24/7.",
                "No security threats detected in the last 24 hours.",
                "Please follow the security guidelines when accessing sensitive data.",
                "Two-factor authentication is recommended for all admin accounts.",
                "Security audit completed successfully with no issues found."
            ],
            general: [
                "Welcome to the general discussion! Feel free to share your thoughts.",
                "That's an interesting point! What does everyone else think?",
                "Thanks for sharing! This could be a great addition to our roadmap.",
                "Let's brainstorm some solutions for this challenge.",
                "Great collaboration everyone! These discussions really help improve DSCMS."
            ]
        };

        const contactResponses = responses[currentContact] || responses.support;
        const randomResponse = contactResponses[Math.floor(Math.random() * contactResponses.length)];

        const responseMessage = {
            id: Date.now(),
            sender: currentContactName,
            message: randomResponse,
            timestamp: new Date().toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }),
            is_own: false
        };

        messages.push(responseMessage);
        renderMessages();
    }

    function renderMessages() {
        if (!chatMessages) return;

        if (messages.length === 0) {
            loadContactMessages(currentContact);
            return;
        }

        chatMessages.innerHTML = messages.map(message => {
            const avatarText = message.sender.split(' ').map(word => word.charAt(0)).join('').substring(0, 2);
            const messageClass = message.is_own ? 'chat-message sent' : 'chat-message';
            const avatarClass = message.is_own ? 'message-avatar bg-primary' : 'message-avatar bg-success';

            return `
                <div class="${messageClass}">
                    <div class="${avatarClass}">${avatarText}</div>
                    <div class="message-content">
                        <div class="message-bubble">
                            ${escapeHtml(message.message)}
                        </div>
                        <div class="message-time">${message.timestamp}</div>
                    </div>
                </div>
            `;
        }).join('');

        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function setInputState(enabled) {
        if (messageInput) messageInput.disabled = !enabled;
        if (sendButton) sendButton.disabled = !enabled;

        if (enabled && messageInput) {
            messageInput.focus();
        }
    }

    function showNotification(message, type = 'info') {
        // You can integrate with your existing notification system here
        console.log(`[${type.toUpperCase()}] ${message}`);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Auto-focus message input
    if (messageInput) {
        messageInput.focus();
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991 && chatSidebar && chatSidebar.classList.contains('show')) {
            chatSidebar.classList.remove('show');
        }
    });

    console.log('DSCMS Chat App - Initialized successfully');
});
</script>
@endsection
