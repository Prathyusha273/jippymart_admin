<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Update</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
        }
        .status-new { background-color: #28a745; color: white; }
        .status-accepted { background-color: #007bff; color: white; }
        .status-rejected { background-color: #dc3545; color: white; }
        .status-completed { background-color: #28a745; color: white; }
        .status-pending { background-color: #ffc107; color: #212529; }
        .status-shipped { background-color: #17a2b8; color: white; }
        .status-transit { background-color: #6f42c1; color: white; }
        
        .content {
            padding: 30px 20px;
        }
        .order-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        .detail-value {
            color: #212529;
        }
        .customer-info, .restaurant-info {
            background-color: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .info-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .timestamp {
            color: #6c757d;
            font-size: 14px;
            text-align: center;
            margin: 20px 0;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .header {
                padding: 20px 15px;
            }
            .content {
                padding: 20px 15px;
            }
            .detail-row {
                flex-direction: column;
            }
            .detail-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Order Status Update</h1>
            <div class="status-badge status-{{ strtolower(str_replace(' ', '-', $orderStatus)) }}">
                {{ $orderStatus }}
            </div>
        </div>
        
        <div class="content">
            <div class="timestamp">
                {{ date('F j, Y \a\t g:i A') }}
            </div>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value"><strong>#{{ $orderData['id'] ?? 'N/A' }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Type:</span>
                    <span class="detail-value">{{ $orderData['takeAway'] ? 'Takeaway' : 'Delivery' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Amount:</span>
                    <span class="detail-value amount">{{ $orderData['amount'] ?? '₹0.00' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">{{ $orderData['paymentMethod'] ?? 'N/A' }}</span>
                </div>
                @if(isset($orderData['estimatedTimeToPrepare']))
                <div class="detail-row">
                    <span class="detail-label">Prep Time:</span>
                    <span class="detail-value">{{ $orderData['estimatedTimeToPrepare'] }} minutes</span>
                </div>
                @endif
            </div>

            @if(isset($orderData['author']) && $orderData['author'])
            <div class="customer-info">
                <div class="info-title">👤 Customer Information</div>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">{{ $orderData['author']['firstName'] ?? '' }} {{ $orderData['author']['lastName'] ?? '' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $orderData['author']['phoneNumber'] ?? 'N/A' }}</span>
                </div>
                @if(isset($orderData['address']))
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">{{ $orderData['address']['address'] ?? 'N/A' }}</span>
                </div>
                @endif
            </div>
            @endif

            @if(isset($orderData['vendor']) && $orderData['vendor'])
            <div class="restaurant-info">
                <div class="info-title">🏪 Restaurant Information</div>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">{{ $orderData['vendor']['title'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $orderData['vendor']['phoneNumber'] ?? 'N/A' }}</span>
                </div>
            </div>
            @endif

            @if(isset($orderData['driver']) && $orderData['driver'])
            <div class="restaurant-info">
                <div class="info-title">🚗 Driver Information</div>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">{{ $orderData['driver']['firstName'] ?? '' }} {{ $orderData['driver']['lastName'] ?? '' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $orderData['driver']['phoneNumber'] ?? 'N/A' }}</span>
                </div>
            </div>
            @endif

            @if(isset($orderData['products']) && count($orderData['products']) > 0)
            <div class="restaurant-info">
                <div class="info-title">📦 Order Items</div>
                @foreach($orderData['products'] as $product)
                <div class="detail-row">
                    <span class="detail-label">{{ $product['name'] ?? 'Unknown Item' }} x{{ $product['quantity'] ?? 1 }}</span>
                    <span class="detail-value">{{ $product['price'] ?? '₹0.00' }}</span>
                </div>
                @endforeach
            </div>
            @endif

            @if($orderStatus === 'Order Rejected' && isset($orderData['rejectionReason']))
            <div class="restaurant-info" style="background-color: #f8d7da; border: 1px solid #f5c6cb;">
                <div class="info-title" style="color: #721c24;">❌ Rejection Reason</div>
                <p style="color: #721c24; margin: 0;">{{ $orderData['rejectionReason'] }}</p>
            </div>
            @endif
        </div>
        
        <div class="footer">
            <p><strong>JippyMart Order Management System</strong></p>
            <p>This is an automated notification. Please do not reply to this email.</p>
            <p>For support, contact: support@jippymart.in</p>
            <p>Sent to: {{ implode(', ', $adminEmails) }}</p>
        </div>
    </div>
</body>
</html>
