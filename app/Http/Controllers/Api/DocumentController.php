<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Ensure Log facade is imported
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class DocumentController extends Controller
{
    /**
     * Allows an authenticated API client (e.g., Java server) to download
     * a user's business document.
     *
     * This method expects to be protected by 'auth:sanctum' middleware.
     *
     * @param Request $request
     * @param User $user The user whose document is being requested (via route model binding).
     * @return StreamedResponse|JsonResponse
     */
    public function downloadBusinessDocument(Request $request, $userId): StreamedResponse|JsonResponse
    {
        Log::info('API [DEBUG]: downloadBusinessDocument method hit with userId: ' . $userId); // <-- ADD THIS LINE

        $user = User::find($userId); // Manually find the user

        if (!$user) {
            Log::error('API [DEBUG]: User not found with ID during manual fetch.', ['target_user_id' => $userId]);
            return response()->json(['error' => 'User not found.'], 404);
        }

        // Optional: Check for specific token abilities if you defined them when issuing the token.
        // This assumes the authenticated user via token is the one making the request.
        // $authenticatedApiUser = $request->user();
        // if ($authenticatedApiUser && !$authenticatedApiUser->tokenCan('read:documents')) {
        //     Log::warning('API: Unauthorized attempt to read document due to missing ability.', [
        //         'requesting_api_user_id' => $authenticatedApiUser->id,
        //         'target_user_id' => $user->id
        //     ]);
        //     return response()->json(['error' => 'Forbidden: Your API token lacks the required permissions (read:documents).'], 403);
        // }

        if (empty($user->business_document_path)) {
            Log::info('API: Business document download requested, but no document path found for user.', [
                'target_user_id' => $user->id,
                'requesting_api_user_id' => $request->user() ? $request->user()->id : 'unknown (token issue?)'
            ]);
            return response()->json(['error' => 'Business document not found for the specified user.'], 404);
        }

        $filePath = $user->business_document_path;

        // This check was confirmed true in Tinker, so it should still be true
        if (!Storage::disk('private')->exists($filePath)) {
            Log::error('API: Document path exists in DB, but file is missing on "private" disk (unexpected after Tinker test).', [
                'target_user_id' => $user->id,
                'file_path' => $filePath,
                'requesting_api_user_id' => $request->user() ? $request->user()->id : 'unknown'
            ]);
            return response()->json(['error' => 'File not found on storage. It may have been deleted or is inaccessible.'], 404);
        }

        $originalFilename = basename($filePath);
        $downloadFilename = 'user_' . $user->id . '_business_doc_' . $originalFilename;

        Log::info('API: Serving business document for user.', [
            'target_user_id' => $user->id,
            'file_path' => $filePath,
            'download_filename' => $downloadFilename,
            'requesting_api_user_id' => $request->user() ? $request->user()->id : 'unknown'
        ]);

        return Storage::disk('private')->download($filePath, $downloadFilename);
    }

    /**
     * Placeholder for the Java server to update the verification status of a user/document.
     * This method expects to be protected by 'auth:sanctum' middleware.
     *
     * @param Request $request
     * @param User $user The user whose document verification status is being updated.
     * @return JsonResponse
     */
    public function updateVerificationStatus(Request $request, User $user): JsonResponse
    {
        // Optional: Check for specific token abilities
        // $authenticatedApiUser = $request->user();
        // if ($authenticatedApiUser && !$authenticatedApiUser->tokenCan('update:verification')) {
        //     Log::warning('API: Unauthorized attempt to update verification status due to missing ability.', [
        //         'requesting_api_user_id' => $authenticatedApiUser->id,
        //         'target_user_id' => $user->id
        //     ]);
        //     return response()->json(['error' => 'Forbidden: Your API token lacks the required permissions (update:verification).'], 403);
        // }

        $validated = $request->validate([
            'verified' => 'required|boolean',
            'verification_notes' => 'nullable|string|max:1000', // Optional notes from the Java processor
        ]);

        try {
            $user->verified = $validated['verified'];
            // You might want to add a new column to your users table for 'verification_notes'
            // if ($request->has('verification_notes')) {
            //    $user->verification_notes = $validated['verification_notes'];
            // }
            // You might also want a 'verified_by_service_at' timestamp
            // $user->verified_at = now(); // Or use the existing email_verified_at if appropriate, or a new column

            $user->save();

            Log::info('API: User document verification status updated.', [
                'target_user_id' => $user->id,
                'new_status' => $user->verified,
                'notes' => $validated['verification_notes'] ?? 'N/A',
                'requesting_api_user_id' => $request->user() ? $request->user()->id : 'unknown'
            ]);

            // Notify the user (optional)
            // if ($user->verified) {
            //     $user->notify(new \App\Notifications\AccountVerifiedNotification());
            // } else {
            //     $user->notify(new \App\Notifications\VerificationFailedNotification($validated['verification_notes'] ?? 'No specific reason provided.'));
            // }

            return response()->json([
                'message' => 'User verification status updated successfully.',
                'user_id' => $user->id,
                'verified_status' => $user->verified,
            ]);
        } catch (\Exception $e) {
            Log::error('API: Failed to update user verification status.', [
                'target_user_id' => $user->id,
                'error' => $e->getMessage(),
                'requesting_api_user_id' => $request->user() ? $request->user()->id : 'unknown'
            ]);
            return response()->json(['error' => 'Failed to update verification status.'], 500);
        }
    }
}
