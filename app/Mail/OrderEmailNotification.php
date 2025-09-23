<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderEmailNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $orderData;
    public $orderStatus;
    public $adminEmails;

    /**
     * Create a new message instance.
     *
     * @param array $orderData
     * @param string $orderStatus
     * @param array $adminEmails
     */
    public function __construct($orderData, $orderStatus, $adminEmails = [])
    {
        $this->orderData = $orderData;
        $this->orderStatus = $orderStatus;
        $this->adminEmails = $adminEmails;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->getEmailSubject();
        
        return $this->subject($subject)
                    ->view('emails.order-notification')
                    ->with([
                        'orderData' => $this->orderData,
                        'orderStatus' => $this->orderStatus,
                        'adminEmails' => $this->adminEmails
                    ]);
    }

    /**
     * Get email subject based on order status
     */
    private function getEmailSubject()
    {
        $orderId = $this->orderData['id'] ?? 'N/A';
        
        switch ($this->orderStatus) {
            case 'Order Placed':
                return "🆕 New Order Received - Order #{$orderId}";
            case 'Order Accepted':
                return "✅ Order Accepted - Order #{$orderId}";
            case 'Order Rejected':
                return "❌ Order Rejected - Order #{$orderId}";
            case 'Order Completed':
                return "🎉 Order Completed - Order #{$orderId}";
            case 'Driver Pending':
                return "⏳ Driver Assignment Pending - Order #{$orderId}";
            case 'Driver Rejected':
                return "🚫 Driver Rejected Order - Order #{$orderId}";
            case 'Order Shipped':
                return "🚚 Order Shipped - Order #{$orderId}";
            case 'In Transit':
                return "🚛 Order In Transit - Order #{$orderId}";
            default:
                return "📋 Order Status Update - Order #{$orderId}";
        }
    }
}
