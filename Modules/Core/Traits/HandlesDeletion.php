<?php

namespace Modules\Core\Traits;

use Illuminate\Http\RedirectResponse;
use Modules\Core\Support\TranslationHelper;

/**
 * Trait for standardized delete operations across controllers.
 *
 * Implements DRY principle by centralizing common delete logic.
 * Implements SOLID principles with single responsibility and open/closed.
 * Uses early returns for better code readability.
 */
trait HandlesDeletion
{
    /**
     * Execute a delete operation with standardized error handling.
     *
     * @param callable    $deleteCallback The deletion logic to execute
     * @param string      $redirectRoute  Route to redirect to after deletion
     * @param string|null $successMessage Custom success message (optional)
     * @param string|null $errorMessage   Custom error message (optional)
     *
     * @return RedirectResponse
     */
    protected function executeDelete(
        callable $deleteCallback,
        string $redirectRoute,
        ?string $successMessage = null,
        ?string $errorMessage = null
    ): RedirectResponse {
        try {
            // Execute the delete operation
            $result = $deleteCallback();

            // Early return on failure
            if ($result === false) {
                return $this->redirectWithError(
                    $redirectRoute,
                    $errorMessage ?? TranslationHelper::trans('record_deletion_failed')
                );
            }

            // Success case
            return $this->redirectWithSuccess(
                $redirectRoute,
                $successMessage ?? TranslationHelper::trans('record_successfully_deleted')
            );
        } catch (\Exception $e) {
            // Log the error for debugging
            if (function_exists('log_message')) {
                log_message('error', 'Delete operation failed: ' . $e->getMessage());
            }

            // Return with error message
            return $this->redirectWithError(
                $redirectRoute,
                $errorMessage ?? TranslationHelper::trans('record_deletion_failed')
            );
        }
    }

    /**
     * Redirect with success message.
     *
     * @param string $route
     * @param string $message
     *
     * @return RedirectResponse
     */
    protected function redirectWithSuccess(string $route, string $message): RedirectResponse
    {
        return redirect()->route($route)
            ->with('alert_success', $message);
    }

    /**
     * Redirect with error message.
     *
     * @param string $route
     * @param string $message
     *
     * @return RedirectResponse
     */
    protected function redirectWithError(string $route, string $message): RedirectResponse
    {
        return redirect()->route($route)
            ->with('alert_error', $message);
    }

    /**
     * Verify user has permission to delete a resource.
     *
     * @param int|null $userId       Current user ID
     * @param int|null $resourceOwnerId Resource owner ID (if applicable)
     *
     * @return bool
     */
    protected function canDelete(?int $userId, ?int $resourceOwnerId = null): bool
    {
        // Early return if no user ID
        if ($userId === null) {
            return false;
        }

        // If no resource owner specified, user is authorized
        if ($resourceOwnerId === null) {
            return true;
        }

        // Check if user owns the resource
        return $userId === $resourceOwnerId;
    }
}
