<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    protected $firestore;
    protected $collection = 'activity_logs';

    public function __construct()
    {
        $this->firestore = new FirestoreClient([
            'projectId' => config('firestore.project_id'),
            'keyFilePath' => config('firestore.credentials'),
            'databaseId' => config('firestore.database_id'),
        ]);
        
        $this->collection = config('firestore.collection', 'activity_logs');
    }

    /**
     * Log an activity to Firestore
     *
     * @param mixed $user The authenticated user
     * @param string $module The module name (e.g., 'cuisines', 'orders')
     * @param string $action The action performed (e.g., 'created', 'updated', 'deleted')
     * @param string $description Description of the action
     * @param Request|null $request The HTTP request object
     * @return bool
     */
    public function log($user, $module, $action, $description, Request $request = null)
    {
        try {
            // Get user information
            $userType = $this->getUserType($user);
            $role = $this->getUserRole($user);
            
            // Get request information
            $ipAddress = $request ? $request->ip() : request()->ip();
            $userAgent = $request ? $request->userAgent() : request()->userAgent();

            // Prepare log data
            $logData = [
                'user_id' => $user->id ?? $user->uid ?? 'unknown',
                'user_type' => $userType,
                'role' => $role,
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime())
            ];

            // Add to Firestore
            $this->firestore->collection($this->collection)->add($logData);

            return true;
        } catch (\Exception $e) {
            \Log::error('Activity Logger Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user type based on user object
     *
     * @param mixed $user
     * @return string
     */
    protected function getUserType($user)
    {
        if (!$user) {
            return 'unknown';
        }

        // Check if user has role_id (admin user)
        if (isset($user->role_id)) {
            return 'admin';
        }

        // Check for other user types based on user properties
        if (isset($user->user_type)) {
            return $user->user_type;
        }

        // Default to admin if we can't determine
        return 'admin';
    }

    /**
     * Get user role
     *
     * @param mixed $user
     * @return string
     */
    protected function getUserRole($user)
    {
        if (!$user) {
            return 'unknown';
        }

        // If user has role_id, get role name from database
        if (isset($user->role_id)) {
            $role = \App\Models\Role::find($user->role_id);
            return $role ? $role->role_name : 'unknown';
        }

        // Check for role property
        if (isset($user->role)) {
            return $user->role;
        }

        return 'unknown';
    }

    /**
     * Get logs for a specific module
     *
     * @param string $module
     * @param int $limit
     * @return array
     */
    public function getLogsByModule($module, $limit = 100)
    {
        try {
            $query = $this->firestore->collection($this->collection)
                ->where('module', '=', $module)
                ->orderBy('created_at', 'desc')
                ->limit($limit);

            $documents = $query->documents();
            $logs = [];

            foreach ($documents as $document) {
                $data = $document->data();
                $data['id'] = $document->id();
                $logs[] = $data;
            }

            return $logs;
        } catch (\Exception $e) {
            \Log::error('Error fetching activity logs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all logs with pagination
     *
     * @param int $limit
     * @param string|null $startAfter
     * @return array
     */
    public function getAllLogs($limit = 50, $startAfter = null)
    {
        try {
            $query = $this->firestore->collection($this->collection)
                ->orderBy('created_at', 'desc')
                ->limit($limit);

            if ($startAfter) {
                $query = $query->startAfter($startAfter);
            }

            $documents = $query->documents();
            $logs = [];

            foreach ($documents as $document) {
                $data = $document->data();
                $data['id'] = $document->id();
                $logs[] = $data;
            }

            return $logs;
        } catch (\Exception $e) {
            \Log::error('Error fetching all activity logs: ' . $e->getMessage());
            return [];
        }
    }
}
