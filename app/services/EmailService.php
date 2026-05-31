<?php

class EmailService {
    private static $instance;

    private function __construct() {}

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new EmailService();
        }
        return self::$instance;
    }
    public function SendCreateOrderEmail($order_id, $email, $items, $total_sum, $created_at) {
        $itemsHtml = '';
        foreach ($items as $item) {
            $title = htmlspecialchars($item['title']);
            $qty = (int)$item['quantity'];
            $price = $item['price'] / 100;
            $itemTotal = $price * $qty;

            $itemsHtml .= "
                <tr style='border-bottom: 1px solid #eef2f3;'>
                    <td style='padding: 12px 0; font-size: 15px; color: #2d3748;'>{$title}</td>
                    <td style='padding: 12px 10px; font-size: 15px; color: #718096; text-align: center;'>{$qty} шт.</td>
                    <td style='padding: 12px 0; font-size: 15px; font-weight: bold; color: #2d3748; text-align: right; white-space: nowrap;'>" . number_format($itemTotal, 2, '.', ' ') . " руб.</td>
                </tr>
            ";
        }

        $formattedDate = date('d.m.Y в H:i', strtotime($created_at));
        $totalDisplay = number_format($total_sum / 100, 2, '.', ' ');

        $message = "
            <!DOCTYPE html>
            <html lang='ru'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Заказ #{$order_id}</title>
                </head>
                <body style='margin: 0; padding: 0; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
                    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #f4f6f8; padding: 20px 10px;'>
                        <tr>
                            <td align='center'>
                                <table width='100%' max-width='600' border='0' cellspacing='0' cellpadding='0' style='max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                                    <tr>
                                        <td style='padding: 30px;'>
                                            <h2 style='margin: 0 0 5px 0; color: #2d3748; font-size: 22px; font-weight: 700;'>Подтверждение заказа</h2>
                                            <p style='margin: 0 0 25px 0; font-size: 14px; color: #718096;'>Заказ #{$order_id} от {$formattedDate}</p>
                                            
                                            <h3 style='margin: 0 0 15px 0; color: #2d3748; font-size: 16px; border-bottom: 2px solid #0d6efd; padding-bottom: 6px; display: inline-block;'>Email получателя</h3>
                                            <p style='margin: 0 0 30px 0; font-size: 15px; color: #4a5568;'>" . htmlspecialchars($email) . "</p>
                
                                            <h3 style='margin: 0 0 15px 0; color: #2d3748; font-size: 16px; border-bottom: 2px solid #0d6efd; padding-bottom: 6px; display: inline-block;'>Состав заказа</h3>
                                            
                                            <table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin-bottom: 25px;'>
                                                {$itemsHtml}
                                            </table>
                
                                            <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #f8fafc; border-radius: 12px; padding: 20px;'>
                                                <tr>
                                                    <td style='font-size: 16px; font-weight: 600; color: #4a5568;'>Итого к оплате:</td>
                                                    <td style='font-size: 22px; font-weight: 700; color: #0d6efd; text-align: right; white-space: nowrap;'>{$totalDisplay} руб.</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
            </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        return mail($email, "Ваш заказ #{$order_id} успешно оформлен!", $message, $headers);
    }

    public function SendUpdateStatusEmail($order_id, $email, $status, $created_at) {
        $statusConfig = [
            'CREATED' => ['text' => 'Создан', 'color' => '#4b5563'],
            'CONFIRMED' => ['text' => 'В обработке', 'color' => '#d97706'],
            'COMPLETED' => ['text' => 'Доставлен', 'color' => '#16a34a'],
            'CANCELLED' => ['text' => 'Отменен', 'color' => '#dc2626'],
        ];

        $statusInfo = $statusConfig[strtoupper($status)] ?? ['text' => $status, 'color' => '#4b5563'];
        $formattedDate = date('d.m.Y в H:i', strtotime($created_at));

        $message = "
            <!DOCTYPE html>
            <html lang='ru'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Обновление статуса заказа #{$order_id}</title>
                </head>
                <body style='margin: 0; padding: 0; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
                    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #f4f6f8; padding: 20px 10px;'>
                        <tr>
                            <td align='center'>
                                <table width='100%' max-width='600' border='0' cellspacing='0' cellpadding='0' style='max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                                    <tr>
                                        <td style='padding: 30px;'>
                                            <h2 style='margin: 0 0 5px 0; color: #2d3748; font-size: 22px; font-weight: 700;'>Статус заказа изменен</h2>
                                            <p style='margin: 0 0 25px 0; font-size: 14px; color: #718096;'>Заказ #{$order_id} от {$formattedDate}</p>
                                            
                                            <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #f8fafc; border-radius: 12px; padding: 20px; text-align: center; border-left: 4px solid {$statusInfo['color']};'>
                                                <tr>
                                                    <td style='font-size: 16px; color: #4a5568; padding-bottom: 5px;'>Новый статус вашего заказа:</td>
                                                </tr>
                                                <tr>
                                                    <td style='font-size: 20px; font-weight: 700; color: {$statusInfo['color']};'>{$statusInfo['text']}</td>
                                                </tr>
                                            </table>
                                            
                                            <p style='margin: 25px 0 0 0; font-size: 14px; color: #718096; text-align: center;'>Если у вас возникли вопросы, пожалуйста, свяжитесь с поддержкой.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
            </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        return mail($email, "Статус заказа #{$order_id} изменен на \"{$statusInfo['text']}\"", $message, $headers);
    }
}