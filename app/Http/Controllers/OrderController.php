<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Google\Client as Google_Client;
use App\Mail\OrderEmailNotification;

class OrderController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index($id='')
    {
       return view("orders.index")->with('id', $id);
    }
    

 	public function edit($id)
    {
    	return view('orders.edit')->with('id', $id);
    }

    public function sendNotification(Request $request)
    {

        $orderStatus=$request->orderStatus;

        // Send email notifications to admin team
        $this->sendOrderEmailNotification($request);

        if(Storage::disk('local')->has('firebase/credentials.json') && ($orderStatus=="Order Accepted" || $orderStatus=="Order Rejected"|| $orderStatus=="Order Completed" || $orderStatus=="Driver Accepted")){

            $client= new Google_Client();
            $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $client_token = $client->getAccessToken();
            $access_token = $client_token['access_token'];

            $fcm_token = $request->fcm;
            
            if(!empty($access_token) && !empty($fcm_token)){

                $projectId = env('FIREBASE_PROJECT_ID');
                $url = 'https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send';

                $data = [
                    'message' => [
                        'notification' => [
                            'title' => $request->subject,
                            'body' => $request->message,
                        ],
                        'token' => $fcm_token,
                    ],
                ];

                $headers = array(
                    'Content-Type: application/json',
                    'Authorization: Bearer '.$access_token
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                
                $result = curl_exec($ch);
                if ($result === FALSE) {
                    die('FCM Send Error: ' . curl_error($ch));
                }
                curl_close($ch);
                $result=json_decode($result);

                $response = array();
                $response['success'] = true;
                $response['message'] = 'Notification successfully sent.';
                $response['result'] = $result;

            }else{
                $response = array();
                $response['success'] = false;
                $response['message'] = 'Missing sender id or token to send notification.';
            }

        }else{
            $response = array();
            $response['success'] = false;
            $response['message'] = 'Firebase credentials file not found.';
        }
       
        return response()->json($response);
    }

    /**
     * Public method to send email notifications (for direct API calls)
     */
    public function sendOrderEmailNotificationPublic(Request $request)
    {
        $this->sendOrderEmailNotification($request);
        
        return response()->json([
            'success' => true,
            'message' => 'Email notifications sent successfully'
        ]);
    }

    /**
     * Send email notifications to admin team for order updates
     */
    private function sendOrderEmailNotification(Request $request)
    {
        try {
            // Admin email list
            $adminEmails = [
                'info@jippymart.in',
                'mohan@jippymart.in',
                'sivapm@jippymart.in',
                'sudheer@jippymart.in'
            ];

            $orderStatus = $request->orderStatus;
            
            \Log::info("Email notification request received", [
                'order_status' => $orderStatus,
                'order_id' => $request->input('order_id'),
                'request_data' => $request->all()
            ]);
            
            // Only send emails for specific statuses
            if (in_array($orderStatus, ['Order Placed', 'Order Rejected', 'Order Completed'])) {
                
                // Get order data from request or fetch from database
                $orderData = $this->getOrderDataFromRequest($request);
                
                \Log::info("Preparing to send email notification", [
                    'order_status' => $orderStatus,
                    'order_data' => $orderData
                ]);
                
                // Send email to all admin emails
                foreach ($adminEmails as $email) {
                    Mail::to($email)->send(new OrderEmailNotification($orderData, $orderStatus, $adminEmails));
                    \Log::info("Email sent to: {$email}");
                }
                
                \Log::info("Order email notification sent for order status: {$orderStatus}", [
                    'order_id' => $orderData['id'] ?? 'unknown',
                    'recipients' => $adminEmails
                ]);
            } else {
                \Log::info("Email notification skipped - status not in target list", [
                    'order_status' => $orderStatus,
                    'target_statuses' => ['Order Placed', 'Order Rejected', 'Order Completed']
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error("Failed to send order email notification: " . $e->getMessage(), [
                'order_status' => $request->orderStatus ?? 'unknown',
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Extract order data from request
     */
    private function getOrderDataFromRequest(Request $request)
    {
        // Try to get order data from request parameters
        $orderData = [
            'id' => $request->input('order_id', 'N/A'),
            'status' => $request->orderStatus,
            'takeAway' => $request->input('takeAway', false),
            'amount' => $request->input('amount', '₹0.00'),
            'paymentMethod' => $request->input('paymentMethod', 'N/A'),
            'estimatedTimeToPrepare' => $request->input('estimatedTimeToPrepare'),
            'rejectionReason' => $request->input('rejectionReason'),
        ];

        // Add customer information if available
        if ($request->has('customer_name')) {
            $orderData['author'] = [
                'firstName' => $request->input('customer_name'),
                'lastName' => $request->input('customer_lastname', ''),
                'phoneNumber' => $request->input('customer_phone', 'N/A')
            ];
        }

        // Add restaurant information if available
        if ($request->has('vendor_name')) {
            $orderData['vendor'] = [
                'title' => $request->input('vendor_name'),
                'phoneNumber' => $request->input('vendor_phone', 'N/A')
            ];
        }

        // Add driver information if available
        if ($request->has('driver_name')) {
            $orderData['driver'] = [
                'firstName' => $request->input('driver_name'),
                'lastName' => $request->input('driver_lastname', ''),
                'phoneNumber' => $request->input('driver_phone', 'N/A')
            ];
        }

        // Add products if available
        if ($request->has('products')) {
            $orderData['products'] = $request->input('products', []);
        }

        return $orderData;
    }

    public function orderprint($id){
        return view('orders.print')->with('id',$id);
    }
}
