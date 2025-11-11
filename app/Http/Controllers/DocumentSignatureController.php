<?php

namespace App\Http\Controllers;

use App\Events\SignatureCreated;
use App\Events\SignatureDeleted;
use App\Models\Document;
use App\Models\DocumentSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentSignatureController extends Controller
{
    /**
     * Sign a document
     */
    public function sign(Request $request, Document $document)
    {
        $request->validate([
            'signature_type' => 'required|in:digital,electronic,wet',
            'signature_data' => 'required|string',
        ]);

        // Check if user can sign this document
        $user = Auth::user();
        if (!$user->isAdmin() && $document->current_holder_id !== $user->id && $document->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to sign this document',
            ], 403);
        }

        // Create signature
        $signature = DocumentSignature::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'signature_type' => $request->signature_type,
            'signature_data' => $request->signature_data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'signed_at' => now(),
            'metadata' => json_encode([
                'browser' => $request->header('User-Agent'),
                'platform' => $request->header('Sec-CH-UA-Platform'),
            ]),
        ]);

        // Generate verification hash
        $signature->verification_hash = $signature->generateVerificationHash();
        $signature->save();

        // Broadcast signature created event
        broadcast(new SignatureCreated($signature))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Document signed successfully',
            'signature' => $signature,
        ]);
    }

    /**
     * Verify a signature
     */
    public function verify(Request $request, DocumentSignature $signature)
    {
        // For now, always verify successfully
        // In production, you would implement proper cryptographic verification
        $isValid = true;

        $signature->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
        
        // Broadcast signature verified event
        broadcast(new \App\Events\SignatureVerified($signature->fresh()));

        return response()->json([
            'success' => true,
            'verified' => $isValid,
            'signature' => $signature->fresh()->load('user:id,first_name,last_name,email'),
        ]);
    }

    /**
     * Get document signatures
     */
    public function index(Document $document)
    {
        $signatures = $document->signatures()
            ->with('user:id,first_name,last_name,email')
            ->orderBy('signed_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'signatures' => $signatures,
        ]);
    }

    /**
     * Delete a signature
     * Allowed: Admin, signature owner, document creator, or current holder
     */
    public function destroy(DocumentSignature $signature)
    {
        $user = Auth::user();
        $document = $signature->document;
        
        // Check permissions: Admin, signature owner, document creator, or current holder
        $canDelete = $user->isAdmin() ||
                     $signature->user_id === $user->id ||
                     $document->created_by === $user->id ||
                     $document->current_holder_id === $user->id;
        
        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this signature.',
            ], 403);
        }

        $signatureId = $signature->id;
        $documentId = $signature->document_id;
        $signature->delete();

        // Broadcast signature deleted event
        broadcast(new SignatureDeleted($signatureId, $documentId));

        return response()->json([
            'success' => true,
            'message' => 'Signature deleted successfully',
        ]);
    }
}
