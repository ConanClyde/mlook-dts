@extends('layouts.app')

@section('title', 'MLOOK - MLUC Document Tracking System')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 backdrop-blur-sm bg-opacity-90 dark:bg-opacity-90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14">
                <!-- Logo -->
                <a href="#" onclick="event.preventDefault(); window.scrollTo({ top: 0, behavior: 'smooth' });" class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.svg') }}" alt="MLOOK Logo" class="h-8 w-auto">
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">MLOOK</span>
                </a>

                <!-- Right Side: Dark Mode + Auth Buttons -->
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <!-- Desktop: Dark Mode Toggle + Auth Buttons -->
                    <div class="hidden md:flex items-center space-x-4">
                        <x-dark-mode-toggle />
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-primary text-sm">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition">Login</a>
                        <a href="{{ route('register') }}" class="btn-primary text-sm">Get Started</a>
                    @endguest
                </div>

                <!-- Mobile: Dark Mode Toggle + Hamburger -->
                <div class="flex md:hidden items-center space-x-3">
                    <x-dark-mode-toggle />
                    <button id="mobile-menu-toggle" type="button" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg id="hamburger-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                </div>
            </div>
        </div>

    </nav>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="fixed inset-0 bg-gray-900/50 dark:bg-black/50 z-40 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

    <!-- Mobile Menu Dropdown (Absolute positioned) -->
    <div id="mobile-menu" class="fixed top-14 left-0 right-0 md:hidden bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 shadow-lg transform -translate-y-full transition-transform duration-300" style="z-index: 45;">
        <div class="px-4 py-4 space-y-3">
            @auth
                <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 text-sm font-medium text-center text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 text-sm font-medium text-center text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-4 py-2.5 text-sm font-medium text-center text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition">Login</a>
                <a href="{{ route('register') }}" class="block w-full btn-primary text-sm text-center">Get Started</a>
            @endguest
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu functionality
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
            const hamburgerIcon = document.getElementById('hamburger-icon');
            const closeIcon = document.getElementById('close-icon');
            let isMenuOpen = false;

            function openMenu() {
                isMenuOpen = true;
                mobileMenu.classList.remove('-translate-y-full');
                mobileMenu.classList.add('translate-y-0');
                mobileMenuOverlay.classList.remove('hidden');
                setTimeout(() => {
                    mobileMenuOverlay.classList.remove('opacity-0');
                    mobileMenuOverlay.classList.add('opacity-100');
                }, 10);
                hamburgerIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
            }

            function closeMenu() {
                isMenuOpen = false;
                mobileMenu.classList.remove('translate-y-0');
                mobileMenu.classList.add('-translate-y-full');
                mobileMenuOverlay.classList.remove('opacity-100');
                mobileMenuOverlay.classList.add('opacity-0');
                hamburgerIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
                setTimeout(() => {
                    mobileMenuOverlay.classList.add('hidden');
                }, 300);
            }

            if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                if (isMenuOpen) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });
            }

            if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', closeMenu);
            }

            // Cursor glow effect for hero section
            const heroSection = document.getElementById('hero-section');
            const cursorGlow = document.getElementById('cursor-glow');
            
            if (heroSection && cursorGlow) {
                // Set initial position to center
                const rect = heroSection.getBoundingClientRect();
                cursorGlow.style.position = 'absolute';
                cursorGlow.style.left = '50%';
                cursorGlow.style.top = '50%';
                cursorGlow.style.display = 'block';
                
                heroSection.addEventListener('mousemove', function(e) {
                    const rect = heroSection.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    cursorGlow.style.left = x + 'px';
                    cursorGlow.style.top = y + 'px';
                });
                
                heroSection.addEventListener('mouseleave', function() {
                    // Return to center when mouse leaves
                    cursorGlow.style.left = '50%';
                    cursorGlow.style.top = '50%';
                });
            }

            // Cursor glow effect for CTA section
            const ctaSection = document.getElementById('cta');
            const ctaCursorGlow = document.getElementById('cta-cursor-glow');
            
            if (ctaSection && ctaCursorGlow) {
                // Set initial position to center
                ctaCursorGlow.style.position = 'absolute';
                ctaCursorGlow.style.left = '50%';
                ctaCursorGlow.style.top = '50%';
                ctaCursorGlow.style.display = 'block';
                
                ctaSection.addEventListener('mousemove', function(e) {
                    const rect = ctaSection.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    ctaCursorGlow.style.left = x + 'px';
                    ctaCursorGlow.style.top = y + 'px';
                });
                
                ctaSection.addEventListener('mouseleave', function() {
                    // Return to center when mouse leaves
                    ctaCursorGlow.style.left = '50%';
                    ctaCursorGlow.style.top = '50%';
                });
            }
        });

        // FAQ Toggle Function
        function toggleFAQ(id) {
            const content = document.getElementById('faq-content-' + id);
            const icon = document.getElementById('faq-icon-' + id);
            
            // Check if currently open
            const isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';
            
            if (!isOpen) {
                // Close all other FAQs first
                for (let i = 1; i <= 6; i++) {
                    if (i !== id) {
                        const otherContent = document.getElementById('faq-content-' + i);
                        const otherIcon = document.getElementById('faq-icon-' + i);
                        if (otherContent && otherContent.style.maxHeight !== '0px') {
                            otherContent.style.maxHeight = '0px';
                            otherContent.style.opacity = '0';
                            otherContent.style.padding = '0 1.5rem';
                            otherIcon.style.transform = 'rotate(0deg)';
                        }
                    }
                }
                
                // Opening: set padding first
                content.style.padding = '1.5rem';
                content.style.maxHeight = '0px';
                content.style.opacity = '0';
                
                // Use requestAnimationFrame for smooth animation
                // Use a very large max-height to accommodate any content size
                requestAnimationFrame(() => {
                    content.style.maxHeight = '1000px';
                    content.style.opacity = '1';
                });
                
                icon.style.transform = 'rotate(180deg)';
            } else {
                // Closing: smoothly collapse
                // Animate to closed state
                content.style.maxHeight = '0px';
                content.style.opacity = '0';
                content.style.padding = '0 1.5rem';
                
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>

    <!-- Hero Section -->
    <section id="hero-section" class="relative pt-20 pb-32 px-6 overflow-hidden">
        <!-- Dot Pattern Background - Light Mode -->
        <div class="absolute inset-0 opacity-40 dark:hidden" style="background-image: radial-gradient(circle, #9ca3af 1px, transparent 1px); background-size: 24px 24px;"></div>
        <!-- Dot Pattern Background - Dark Mode -->
        <div class="absolute inset-0 opacity-20 hidden dark:block" style="background-image: radial-gradient(circle, #6b7280 1px, transparent 1px); background-size: 24px 24px;"></div>
        
        <!-- Interactive Cursor-Following Decoration -->
        <div id="cursor-glow" class="absolute w-96 h-96 bg-gradient-to-br from-orange-400 via-amber-300 to-orange-300 dark:from-orange-600 dark:via-amber-700 dark:to-orange-500 rounded-full blur-3xl opacity-40 dark:opacity-25 pointer-events-none transition-all duration-700 ease-out" style="transform: translate(-50%, -50%); z-index: 0; left: 50%; top: 50%;"></div>
        
        <div class="max-w-5xl mx-auto relative z-10">
            <div class="text-center">
                <!-- Badge -->
                <div class="inline-flex items-center px-4 py-1.5 rounded-full border border-orange-300 dark:border-orange-700 bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-xs font-medium mb-8">
                    MLUC DOCUMENT TRACKING SYSTEM
                </div>

                <!-- Headline -->
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-5 leading-tight">
                    Track Documents<br class="hidden sm:block"/>
                    <span class="text-orange-500 dark:text-orange-400">Faster than Ever</span>
                </h1>

                <!-- Subtitle -->
                <p class="text-base md:text-lg text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto leading-relaxed">
                    Monitor and manage all university documents in real-time with our efficient tracking system designed for DMMMSU-MLUC.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center mb-16">
                    @guest
                        <a href="{{ route('register') }}" class="btn-primary px-6 py-2.5 shadow-lg">
                            Get started - for free
                        </a>
                        <a href="{{ route('login') }}" class="btn-secondary px-6 py-2.5">
                            Login to MLOOK
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn-primary px-6 py-2.5 shadow-lg">
                            Go to Dashboard
                        </a>
                    @endguest
                </div>

                <!-- Dashboard Preview Mock -->
                <div class="relative mx-auto" style="max-width: 1000px;">
                    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden" style="z-index: 1;">
                        <!-- Mock Dashboard Header -->
                        <div class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                            <div class="flex items-center justify-center">
                                <span class="font-semibold text-gray-900 dark:text-white">Document Statistics</span>
                            </div>
                        </div>
                        <!-- Dynamic Stats -->
                        <div class="p-4 sm:p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6 bg-gradient-to-br from-green-50 dark:from-gray-700 via-white dark:via-gray-800 to-green-50 dark:to-gray-700">
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Documents</p>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_documents'] ?? 0) }}</p>
                                <p class="text-xs {{ ($stats['total_documents_change'] ?? 0) >= 0 ? 'text-orange-600 dark:text-orange-400' : 'text-red-600 dark:text-red-400' }} mt-1">
                                    @if(($stats['total_documents_change'] ?? 0) > 0)↑@elseif(($stats['total_documents_change'] ?? 0) < 0)↓@else→@endif 
                                    {{ abs($stats['total_documents_change'] ?? 0) }}%
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">In Progress</p>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['in_progress'] ?? 0) }}</p>
                                <p class="text-xs {{ ($stats['in_progress_change'] ?? 0) >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }} mt-1">
                                    @if(($stats['in_progress_change'] ?? 0) > 0)↑@elseif(($stats['in_progress_change'] ?? 0) < 0)↓@else→@endif 
                                    {{ abs($stats['in_progress_change'] ?? 0) }}%
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Completed</p>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['completed'] ?? 0) }}</p>
                                <p class="text-xs {{ ($stats['completed_change'] ?? 0) >= 0 ? 'text-orange-600 dark:text-orange-400' : 'text-red-600 dark:text-red-400' }} mt-1">
                                    @if(($stats['completed_change'] ?? 0) > 0)↑@elseif(($stats['completed_change'] ?? 0) < 0)↓@else→@endif 
                                    {{ abs($stats['completed_change'] ?? 0) }}%
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pending</p>
                                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['pending'] ?? 0) }}</p>
                                <p class="text-xs {{ ($stats['pending_change'] ?? 0) >= 0 ? 'text-orange-600 dark:text-orange-400' : 'text-red-600 dark:text-red-400' }} mt-1">
                                    @if(($stats['pending_change'] ?? 0) > 0)↑@elseif(($stats['pending_change'] ?? 0) < 0)↓@else→@endif 
                                    {{ abs($stats['pending_change'] ?? 0) }}%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">Everything you need</h2>
                <p class="text-base text-gray-600 dark:text-gray-300">Powerful features to streamline your document tracking</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="p-6 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-lg transition text-center">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <x-icon name="document-text" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Real-time Tracking</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Monitor document status and location with unique tracking numbers in real-time. Know exactly where your documents are at any moment.</p>
                </div>
                
                <div class="p-6 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-lg transition text-center">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <x-icon name="bolt" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Fast Processing</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Streamlined workflow that reduces processing time and improves efficiency. Get documents approved faster than ever.</p>
                </div>
                
                <div class="p-6 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-lg transition text-center">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <x-icon name="lock-closed" class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Secure & Reliable</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Role-based access control ensures data security and privacy for all users. Your documents are protected.</p>
                </div>

                <div class="p-6 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-lg transition text-center">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <x-icon name="bell" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Smart Notifications</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Get instant notifications via in-app or browser push. Never miss an important document update.</p>
                </div>

                <div class="p-6 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-lg transition text-center">
                    <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <x-icon name="chart-bar" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Analytics & Reports</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Comprehensive analytics and reporting tools to track document flow, processing times, and system performance.</p>
                </div>

                <div class="p-6 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-lg transition text-center">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <x-icon name="user-group" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Role-Based Access</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Customizable permissions for different user roles. Admins, faculty, staff, and students have appropriate access levels.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">How It Works</h2>
                <p class="text-base text-gray-600 dark:text-gray-300">Simple steps to streamline your document workflow</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">1</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Create Document</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Upload your document and fill in the required details. The system automatically generates a unique tracking number.</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">2</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Forward & Track</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Forward documents to the appropriate recipients. Track the document's journey through the approval process in real-time.</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">3</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Review & Approve</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Recipients receive notifications and can review, comment, approve, or return documents with remarks.</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">4</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Complete & Archive</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Once approved, documents are marked as completed and can be archived for future reference.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="py-20 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">Why Choose MLOOK?</h2>
                <p class="text-base text-gray-600 dark:text-gray-300">Experience the benefits of digital document management</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                            <x-icon name="check-circle" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Eliminate Paper Trails</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Go completely paperless with digital document management. Reduce printing costs and environmental impact.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <x-icon name="check-circle" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Reduce Processing Time</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Cut document processing time by up to 70%. No more waiting for physical document delivery.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <x-icon name="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Improve Accountability</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Complete audit trail of every action. Know who did what, when, and why.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <x-icon name="check-circle" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">24/7 Access</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Access your documents anytime, anywhere. Work remotely without any limitations.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                            <x-icon name="check-circle" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Centralized Management</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">All documents in one place. Easy search, filter, and organize your entire document library.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                            <x-icon name="check-circle" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Enhanced Collaboration</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Work together seamlessly with comments, forwarding, and real-time updates.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-white dark:bg-gray-800">
        <div class="max-w-4xl mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">Frequently Asked Questions</h2>
                <p class="text-base text-gray-600 dark:text-gray-300">Everything you need to know about MLOOK Document Tracking System</p>
            </div>
            
            <div class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button onclick="toggleFAQ(1)" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <span class="text-base font-semibold text-gray-900 dark:text-white">What is MLOOK Document Tracking System?</span>
                        <svg id="faq-icon-1" class="w-5 h-5 text-gray-500 dark:text-gray-400 transform transition-transform duration-300 ease-in-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="faq-content-1" class="overflow-hidden transition-all duration-300 ease-in-out" style="max-height: 0; opacity: 0; padding: 0;">
                        <p class="text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                            MLOOK is a comprehensive document tracking system designed for DMMMSU-MLUC. It allows you to create, track, route, and manage documents digitally with real-time status updates, ensuring efficient document workflow and accountability.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button onclick="toggleFAQ(2)" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <span class="text-base font-semibold text-gray-900 dark:text-white">Who can use the system?</span>
                        <svg id="faq-icon-2" class="w-5 h-5 text-gray-500 dark:text-gray-400 transform transition-transform duration-300 ease-in-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="faq-content-2" class="overflow-hidden transition-all duration-300 ease-in-out" style="max-height: 0; opacity: 0; padding: 0;">
                        <p class="text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                            The system is available to all DMMMSU-MLUC staff, faculty, administrators, and authorized personnel. Different user roles (Admin, Registrar, Dean, Department Head, Faculty, Staff, Student) have access to different features based on their permissions.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button onclick="toggleFAQ(3)" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <span class="text-base font-semibold text-gray-900 dark:text-white">How do I track a document?</span>
                        <svg id="faq-icon-3" class="w-5 h-5 text-gray-500 dark:text-gray-400 transform transition-transform duration-300 ease-in-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="faq-content-3" class="overflow-hidden transition-all duration-300 ease-in-out" style="max-height: 0; opacity: 0; padding: 0;">
                        <p class="text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                            Every document receives a unique tracking number when created. You can view the document status, current location, routing history, and all actions taken in the document details page. The system provides real-time updates on document progress.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button onclick="toggleFAQ(4)" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <span class="text-base font-semibold text-gray-900 dark:text-white">What types of documents can I create?</span>
                        <svg id="faq-icon-4" class="w-5 h-5 text-gray-500 dark:text-gray-400 transform transition-transform duration-300 ease-in-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="faq-content-4" class="overflow-hidden transition-all duration-300 ease-in-out" style="max-height: 0; opacity: 0; padding: 0;">
                        <p class="text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                            You can create various document types such as memorandums, letters, reports, and other official documents. The available document types depend on your user role. Admins can configure document types and specify which roles can create each type.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button onclick="toggleFAQ(5)" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <span class="text-base font-semibold text-gray-900 dark:text-white">How do I get notified about document updates?</span>
                        <svg id="faq-icon-5" class="w-5 h-5 text-gray-500 dark:text-gray-400 transform transition-transform duration-300 ease-in-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="faq-content-5" class="overflow-hidden transition-all duration-300 ease-in-out" style="max-height: 0; opacity: 0; padding: 0;">
                        <p class="text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                            You can configure your notification preferences in the Settings page. The system supports in-app notifications and browser push notifications. You'll be notified when documents are forwarded to you, when actions are taken on your documents, or when status changes occur.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button onclick="toggleFAQ(6)" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <span class="text-base font-semibold text-gray-900 dark:text-white">Is my data secure?</span>
                        <svg id="faq-icon-6" class="w-5 h-5 text-gray-500 dark:text-gray-400 transform transition-transform duration-300 ease-in-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="faq-content-6" class="overflow-hidden transition-all duration-300 ease-in-out" style="max-height: 0; opacity: 0; padding: 0;">
                        <p class="text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                            Yes, security is a top priority. The system uses role-based access control through document types, ensuring users can only access documents and features appropriate for their role. All document actions are logged in an audit trail. We also support two-factor authentication (2FA) for enhanced account security.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="cta" class="relative py-20 bg-orange-50 dark:bg-orange-900/20 overflow-hidden">
        <!-- Dot Pattern Background - Light Mode -->
        <div class="absolute inset-0 opacity-20 dark:hidden" style="background-image: radial-gradient(circle, #f97316 1px, transparent 1px); background-size: 24px 24px;"></div>
        <!-- Dot Pattern Background - Dark Mode -->
        <div class="absolute inset-0 opacity-10 hidden dark:block" style="background-image: radial-gradient(circle, #fb923c 1px, transparent 1px); background-size: 24px 24px;"></div>
        
        <!-- Interactive Cursor-Following Decoration -->
        <div id="cta-cursor-glow" class="absolute w-96 h-96 bg-gradient-to-br from-orange-400 via-amber-300 to-orange-300 dark:from-orange-600 dark:via-amber-700 dark:to-orange-500 rounded-full blur-3xl opacity-40 dark:opacity-25 pointer-events-none transition-all duration-700 ease-out" style="transform: translate(-50%, -50%); z-index: 0; left: 50%; top: 50%;"></div>
        
        <div class="max-w-4xl mx-auto px-6 relative z-10">
            <div class="text-center">
                <!-- Heading -->
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4 leading-tight">
                    Ready to Transform Your<br class="hidden md:block"/>
                    <span class="text-orange-500 dark:text-orange-400">Document Management?</span>
                </h2>
                
                <!-- Description -->
                <p class="text-base md:text-lg text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto leading-relaxed">
                    Join DMMMSU-MLUC in revolutionizing document management. Start tracking your documents today and experience the future of paperless workflows.
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                    @guest
                        <a href="{{ route('register') }}" class="btn-primary px-6 py-2.5 shadow-lg">
                            Get Started Free
                        </a>
                        <a href="{{ route('login') }}" class="btn-secondary px-6 py-2.5">
                            Login to Account
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn-primary px-6 py-2.5 shadow-lg">
                            Go to Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 py-8">
        <div class="max-w-7xl mx-auto px-6">
            <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} MLOOK - Don Mariano Marcos Memorial State University, Mid La Union Campus. All rights reserved.
            </p>
        </div>
    </footer>
</div>
@endsection
