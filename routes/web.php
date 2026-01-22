<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\AIRateLimit;
use App\Http\Middleware\CheckSubscription;

// ============================================================================
// CONTROLLER IMPORTS
// ============================================================================
use App\Http\Controllers\Auth\{
    AuthenticatedSessionController,
    RegisteredUserController,
    PasswordResetLinkController,
    NewPasswordController,
    EmailVerificationPromptController,
    VerifyEmailController,
    EmailVerificationNotificationController
};

use App\Http\Controllers\{
    HomeController,
    JournalController,
    ProfileController,
    ConnectionController,
    NetworkController,
    PostController,
    CommentController,
    NotificationController,
    FileUploadController,
    PaymentController,
    FeedbackController
};

use App\Http\Controllers\AI\StreamingController;

// ============================================================================
// ROOT REDIRECT
// ============================================================================
Route::get('/', function () {
    return redirect()->route('login');
});

// ============================================================================
// GUEST ROUTES (Unauthenticated Users)
// ============================================================================
Route::middleware('guest')->group(function () {
    // Registration
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Login
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Password Reset
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// ============================================================================
// EMAIL VERIFICATION
// ============================================================================
Route::middleware('auth')->group(function () {
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// ============================================================================
// AUTHENTICATED ROUTES (All routes below require authentication)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // ============================================================================
    // AUTHENTICATION
    // ============================================================================
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // ============================================================================
    // DASHBOARD & NAVIGATION
    // ============================================================================
    
    // Dashboard redirects to Network Home
    Route::get('/dashboard', function() {
        return redirect()->route('network.home');
    })->name('dashboard');
    
    // AI Studio redirects to Journal Creation
    Route::get('/create_journal', [JournalController::class, 'create'])->name('create_journal');

    // ============================================================================
    // PROFILE MANAGEMENT
    // ============================================================================
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/{user}', [ProfileController::class, 'show'])->name('profile.view');
    });

    // ============================================================================
    // JOURNAL MANAGEMENT (Core Feature) with ownership middleware
    // ============================================================================
    Route::prefix('journal')->middleware(['journal.owner'])->group(function () {
        // Create & Edit
        Route::get('/create', [JournalController::class, 'create'])->name('journal.create');
        Route::get('/{journal}/edit', [JournalController::class, 'edit'])->name('journal.edit');
        
        // Preview & Download
        Route::get('/{journal}/preview', [JournalController::class, 'preview'])->name('journal.preview');
        Route::get('/{journal}/download', [JournalController::class, 'download'])->name('journal.download');
        
        // Post for Review
        Route::post('/{journal}/post-review', [JournalController::class, 'postForReview'])->name('journal.post.review');
        
        // Improve with Feedback
        Route::post('/{journal}/improve', [JournalController::class, 'improveWithFeedback'])->name('journal.improve');
    });

    // ============================================================================
    // AI STREAMING & GENERATION (Real-time AI Features)
    // ============================================================================
    Route::prefix('ai')->middleware(['subscription', 'ai.rate.limit'])->group(function () {
        // Journal Generation Streaming
        Route::post('/stream/{journal?}', [StreamingController::class, 'streamJournal'])
            ->name('ai.stream.journal');
        
        // Section Enhancement Streaming
        Route::post('/stream-section', [StreamingController::class, 'streamSectionEnhancement'])
            ->name('ai.stream.section');
        
        // Status Checking
        Route::get('/status/{journal}', [StreamingController::class, 'checkStatus'])
            ->name('ai.status');
        
        // Provider Testing
        Route::get('/test-providers', [StreamingController::class, 'testProviders'])
            ->name('ai.test.providers');
        
        // Admin: System-wide Stats
        Route::get('/system-stats', [StreamingController::class, 'getSystemStats'])
            ->middleware('can:view-system-stats')
            ->name('ai.system.stats');
    });

    // ============================================================================
    // ACADEMIC NETWORK (Social Features)
    // ============================================================================
    Route::prefix('network')->group(function () {
        Route::get('/', [NetworkController::class, 'home'])->name('network.home');
        Route::get('/my-network', [NetworkController::class, 'myNetwork'])->name('network.my-network');
        Route::get('/journals', [NetworkController::class, 'journals'])->name('network.journals');
        Route::get('/reviews', [NetworkController::class, 'reviews'])->name('network.reviews');
        Route::get('/groups', [NetworkController::class, 'groups'])->name('network.groups');
        Route::get('/events', [NetworkController::class, 'events'])->name('network.events');
        Route::get('/jobs', [NetworkController::class, 'jobs'])->name('network.jobs');
    });

    // ============================================================================
    // POSTS & FEEDBACK (with visibility middleware)
    // ============================================================================
    Route::prefix('posts')->middleware(['post.visibility'])->group(function () {
        Route::get('/{post}', [PostController::class, 'show'])->name('posts.show');
        Route::post('/{post}/comment', [PostController::class, 'comment'])->name('posts.comment');
    });

    Route::prefix('feedback')->middleware(['feedback.reviewer'])->group(function () {
        Route::put('/{feedback}', [FeedbackController::class, 'update'])->name('feedback.update');
        Route::delete('/{feedback}', [FeedbackController::class, 'destroy'])->name('feedback.destroy');
    });

    // ============================================================================
    // API ENDPOINTS (AJAX/JSON Endpoints)
    // ============================================================================
    Route::prefix('api')->group(function () {
        // ------------------------------------------------------------
        // FILE UPLOAD API
        // ------------------------------------------------------------
        Route::prefix('upload')->group(function () {
            Route::post('/file', [FileUploadController::class, 'uploadFile'])->name('api.upload.file');
            Route::post('/profile-picture', [FileUploadController::class, 'uploadProfilePicture'])->name('api.upload.profile-picture');
            Route::post('/annex', [FileUploadController::class, 'uploadAnnex'])->name('api.upload.annex');
            Route::post('/figure', [FileUploadController::class, 'uploadFigure'])->name('api.upload.figure');
            Route::delete('/delete', [FileUploadController::class, 'deleteFile'])->name('api.upload.delete');
            Route::get('/disk-usage', [FileUploadController::class, 'getDiskUsage'])->name('api.upload.disk-usage');
            Route::get('/list', [FileUploadController::class, 'listFiles'])->name('api.upload.list');
        });

        // ------------------------------------------------------------
        // JOURNAL API
        // ------------------------------------------------------------
        Route::prefix('journal')->group(function () {
            Route::post('/save', [JournalController::class, 'saveJournal'])->name('api.journal.save');
            Route::post('/save-ai-draft', [JournalController::class, 'saveAIDraft'])->name('api.journal.save.ai.draft');
            Route::post('/{journal}/save-section', [JournalController::class, 'saveSection'])->name('api.journal.save.section');
            Route::post('/{journal}/post-for-review', [JournalController::class, 'postForReview'])->name('api.journal.post.review');
            Route::post('/{journal}/submit-feedback', [JournalController::class, 'submitFeedback'])->name('api.journal.submit.feedback');
        });

        // ------------------------------------------------------------
        // AI ENHANCEMENT API
        // ------------------------------------------------------------
        Route::prefix('ai')->middleware(['subscription', 'ai.rate.limit'])->group(function () {
            // Content Enhancement
            Route::post('/enhance-section', [JournalController::class, 'enhanceSection'])->name('api.ai.enhance.section');
            Route::post('/generate-abstract', [JournalController::class, 'generateAbstract'])->name('api.ai.generate.abstract');
            Route::post('/check-grammar', [JournalController::class, 'checkGrammar'])->name('api.ai.check.grammar');
            Route::post('/suggest-references', [JournalController::class, 'suggestReferences'])->name('api.ai.suggest.references');
            
            // Stats & Info
            Route::get('/usage-stats', [JournalController::class, 'getUsageStats'])->name('api.ai.usage.stats');
            Route::get('/providers-status', [JournalController::class, 'getProvidersStatus'])->name('api.ai.providers.status');
            Route::post('/test-provider', [JournalController::class, 'testProvider'])->name('api.ai.test.provider');
            
            // Quick test endpoint
            Route::get('/test-connection', function() {
                try {
                    $client = new \GuzzleHttp\Client();
                    $response = $client->post(config('services.deepseek.endpoint'), [
                        'headers' => [
                            'Authorization' => 'Bearer ' . config('services.deepseek.key'),
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'model' => config('services.deepseek.model'),
                            'messages' => [
                                ['role' => 'user', 'content' => 'Test connection - respond with "OK"']
                            ],
                            'max_tokens' => 10,
                            'stream' => false
                        ],
                        'timeout' => 30
                    ]);
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'AI connection successful',
                        'data' => json_decode($response->getBody(), true)
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->getMessage(),
                        'config' => [
                            'endpoint' => config('services.deepseek.endpoint'),
                            'model' => config('services.deepseek.model'),
                            'has_key' => !empty(config('services.deepseek.key'))
                        ]
                    ], 500);
                }
            })->name('api.ai.test.connection');
        });

        // ------------------------------------------------------------
        // SOCIAL NETWORK API
        // ------------------------------------------------------------
        
        // Connections
        Route::prefix('connections')->group(function () {
            Route::post('/send/{user}', [ConnectionController::class, 'sendRequest'])->name('api.connections.send');
            Route::post('/{connection}/accept', [ConnectionController::class, 'acceptRequest'])->name('api.connections.accept');
            Route::post('/{connection}/reject', [ConnectionController::class, 'rejectRequest'])->name('api.connections.reject');
            Route::delete('/remove/{user}', [ConnectionController::class, 'removeConnection'])->name('api.connections.remove');
            Route::get('/', [ConnectionController::class, 'getConnections'])->name('api.connections.list');
            Route::get('/pending/{user}', [ConnectionController::class, 'getPendingRequests'])->name('api.connections.pending');
        });

        // Posts
        Route::prefix('posts')->group(function () {
            Route::post('/', [PostController::class, 'store'])->name('api.posts.store');
            Route::get('/', [PostController::class, 'index'])->name('api.posts.index');
            Route::post('/{post}/like', [PostController::class, 'like'])->name('api.posts.like');
            Route::post('/{post}/comment', [PostController::class, 'comment'])->name('api.posts.comment');
            Route::get('/{post}/comments', [PostController::class, 'comments'])->name('api.posts.comments');
            Route::delete('/{post}', [PostController::class, 'destroy'])->name('api.posts.destroy');
        });

        // Comments
        Route::prefix('comments')->group(function () {
            Route::post('/{comment}/like', [CommentController::class, 'like'])->name('api.comments.like');
            Route::post('/{comment}/helpful', [CommentController::class, 'markHelpful'])->name('api.comments.helpful');
            Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('api.comments.destroy');
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('api.notifications.index');
            Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.read.all');
            Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('api.notifications.destroy');
        });

        // User Data
        Route::get('/user/journals', function() {
            $journals = Auth::user()->journals()
                ->select('id', 'title', 'abstract', 'area_of_study', 'created_at', 'status', 'ai_percentage')
                ->whereIn('status', ['draft', 'ai_draft', 'under_review', 'published'])
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json([
                'success' => true,
                'journals' => $journals
            ]);
        })->name('api.user.journals');

        // AI Usage Stats for current user
        Route::get('/user/ai-usage', function() {
            $user = Auth::user();
            
            $usage = [
                'ai_usage_count' => $user->ai_usage_count ?? 0,
                'ai_usage_tokens' => $user->ai_usage_tokens ?? 0,
                'free_tier_limit' => config('ai.free_tier_limit', 10),
                'requires_subscription' => config('ai.requires_subscription', false),
                'has_active_subscription' => $user->hasActiveSubscription() ?? false,
            ];
            
            return response()->json([
                'success' => true,
                'usage' => $usage
            ]);
        })->name('api.user.ai.usage');
    });

    // ============================================================================
    // PLACEHOLDER PAGES (To be implemented later)
    // ============================================================================
    Route::get('/notifications', function () {
        return view('notifications');
    })->name('notifications.page');
    
    Route::get('/blog', function () {
        return view('blog');
    })->name('blog');
    
    Route::get('/groups', function () {
        return view('groups');
    })->name('groups.page');
    
    Route::get('/submissions', function () {
        return view('submissions');
    })->name('submissions');
    
    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');

    // ============================================================================
    // SUBSCRIPTION & PAYMENT PAGES
    // ============================================================================
    Route::prefix('subscription')->group(function () {
        Route::get('/', function () {
            return view('subscription.index');
        })->name('subscription.index');
        
        Route::get('/upgrade', function () {
            return view('subscription.upgrade');
        })->name('subscription.upgrade');
        
        Route::get('/manage', function () {
            return view('subscription.manage');
        })->name('subscription.manage');
        
        Route::post('/create-checkout', [PaymentController::class, 'createCheckout'])->name('subscription.create.checkout');
        Route::get('/success', [PaymentController::class, 'success'])->name('subscription.success');
        Route::get('/cancel', [PaymentController::class, 'cancel'])->name('subscription.cancel');
    });

    // ============================================================================
    // TEST ROUTES (Development Only)
    // ============================================================================
    if (app()->environment('local')) {
        Route::get('/test-relationships', function() {
            $user = \App\Models\User::withCount([
                'connections as total_connections_count',
                'sentConnections as pending_sent_count' => function($query) {
                    $query->where('status', 'pending');
                },
                'receivedConnections as pending_received_count' => function($query) {
                    $query->where('status', 'pending');
                }
            ])->first();
            
            if (!$user) {
                return response()->json(['error' => 'No users found'], 404);
            }
            
            return response()->json([
                'user' => $user->full_name,
                'total_connections' => $user->total_connections_count,
                'pending_sent' => $user->pending_sent_count,
                'pending_received' => $user->pending_received_count,
                'journals_count' => $user->journals()->count(),
                'network_posts_count' => $user->networkPosts()->count(),
            ]);
        });

        Route::get('/test-models', function() {
            return response()->json([
                'models' => [
                    'Journal' => \App\Models\Journal::count(),
                    'NetworkPost' => \App\Models\NetworkPost::count(),
                    'ReviewFeedback' => \App\Models\ReviewFeedback::count(),
                    'JournalVersion' => \App\Models\JournalVersion::count(),
                    'AIUsageLog' => \App\Models\AIUsageLog::count(),
                    'User' => \App\Models\User::count(),
                ]
            ]);
        });

        Route::get('/test-ai-connection', function() {
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->post(config('services.deepseek.endpoint'), [
                    'headers' => [
                        'Authorization' => 'Bearer ' . config('services.deepseek.key'),
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => config('services.deepseek.model'),
                        'messages' => [
                            ['role' => 'user', 'content' => 'Test connection - respond with "OK"']
                        ],
                        'max_tokens' => 10,
                        'stream' => false
                    ],
                    'timeout' => 30
                ]);
                
                $data = json_decode($response->getBody(), true);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'AI connection successful',
                    'response' => $data['choices'][0]['message']['content'] ?? 'No content',
                    'model' => $data['model'] ?? 'Unknown',
                    'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'config_check' => [
                        'endpoint' => config('services.deepseek.endpoint'),
                        'model' => config('services.deepseek.model'),
                        'has_key' => !empty(config('services.deepseek.key')),
                        'key_length' => strlen(config('services.deepseek.key') ?? ''),
                    ]
                ], 500);
            }
        })->name('test.ai.connection');

        Route::get('/test-stream', function() {
            return response()->stream(function() {
                echo "data: " . json_encode(['message' => 'Test event 1']) . "\n\n";
                ob_flush();
                flush();
                sleep(1);
                
                echo "data: " . json_encode(['message' => 'Test event 2']) . "\n\n";
                ob_flush();
                flush();
                sleep(1);
                
                echo "data: " . json_encode(['message' => 'Test event 3']) . "\n\n";
                ob_flush();
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
            ]);
        })->name('test.stream');
    }
});

// ============================================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================================

// Payment Callbacks & Webhooks
Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

// Public Journal Download (for published journals only)
Route::get('/public/journal/{journal}/download', [JournalController::class, 'publicDownload'])
    ->name('public.journal.download');

// Health check route (for monitoring)
Route::get('/health', function() {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment(),
        'debug' => config('app.debug'),
    ]);
});

// ============================================================================
// ERROR PAGES
// ============================================================================
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});