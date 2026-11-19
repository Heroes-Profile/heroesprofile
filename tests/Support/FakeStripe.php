<?php

namespace Tests\Support;

use Illuminate\Support\Str;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;

/**
 * Stands in for Stripe's API underneath stripe-php, so Cashier runs its real code
 * against answers shaped like Stripe's. Keeps the customers, payment methods and
 * subscriptions it is told about, and records every request.
 */
class FakeStripe implements ClientInterface
{
    /** @var list<array{method: string, path: string, params: array}> */
    public array $requests = [];

    public array $customers = [];

    public array $paymentMethods = [];

    public array $subscriptions = [];

    public array $invoices = [];

    /** Price id => unit_amount in cents. */
    public array $prices = [];

    /** The next request answers with this Stripe error: [status, message]. */
    public ?array $failNext = null;

    /** Status a new subscription is created in. */
    public string $newSubscriptionStatus = 'active';

    private int $sequence = 0;

    public static function install(): self
    {
        $fake = new self;
        ApiRequestor::setHttpClient($fake);

        return $fake;
    }

    public static function uninstall(): void
    {
        ApiRequestor::setHttpClient(null);
    }

    public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
    {
        $path = parse_url($absUrl, PHP_URL_PATH);
        $this->requests[] = ['method' => strtoupper($method), 'path' => $path, 'params' => $params];

        if ($this->failNext !== null) {
            [$status, $message] = $this->failNext;
            $this->failNext = null;

            return $this->error($status, $message);
        }

        $segments = explode('/', trim(substr($path, strlen('/v1/')), '/'));

        $object = match (true) {
            $method === 'post' && $segments === ['customers'] => $this->createCustomer($params),
            $segments[0] === 'customers' && count($segments) === 2 => $this->expand($this->customer($segments[1], $method === 'post' ? array_diff_key($params, ['expand' => 1]) : []), $params['expand'] ?? []),
            $method === 'post' && $segments === ['setup_intents'] => [
                'id' => $this->id('seti'), 'object' => 'setup_intent', 'customer' => $params['customer'] ?? null,
                'client_secret' => 'seti_secret_test', 'status' => 'requires_payment_method',
            ],
            $segments[0] === 'payment_methods' && ($segments[2] ?? null) === 'attach' => $this->attach($segments[1], $params['customer']),
            $segments[0] === 'payment_methods' && count($segments) === 2 => $this->paymentMethod($segments[1]),
            $method === 'post' && $segments === ['subscriptions'] => $this->createSubscription($params),
            $segments[0] === 'subscriptions' && count($segments) === 2 => match ($method) {
                'get' => $this->expand($this->subscriptions[$segments[1]] ?? null, $params['expand'] ?? []),
                'post' => $this->updateSubscription($segments[1], $params),
                'delete' => $this->deleteSubscription($segments[1]),
            },
            $segments[0] === 'prices' && count($segments) === 2 && isset($this->prices[$segments[1]]) => $this->price($segments[1]),
            $segments[0] === 'subscription_items' && count($segments) === 2 => $this->subscriptionItem($segments[1]),
            $segments[0] === 'invoices' && count($segments) === 2 => $this->invoices[$segments[1]] ?? null,
            $method === 'post' && $segments === ['refunds'] => [
                'id' => $this->id('re'), 'object' => 'refund', 'amount' => (int) $params['amount'],
                'payment_intent' => $params['payment_intent'], 'status' => 'succeeded',
            ],
            default => null,
        };

        return $object === null
            ? $this->error(404, 'FakeStripe has no answer for '.strtoupper($method).' '.$path)
            : [json_encode($object), 200, ['request-id' => 'req_test']];
    }

    /** A customer Stripe already holds, e.g. one the seeder gave a stripe_id. */
    public function addCustomer(string $id, array $extra = []): array
    {
        return $this->customers[$id] = array_merge([
            'id' => $id, 'object' => 'customer', 'email' => null, 'name' => null,
            'invoice_settings' => ['default_payment_method' => null], 'metadata' => [],
        ], $extra);
    }

    public function addPaymentMethod(string $id, string $brand = 'visa', string $last4 = '4242', ?string $customer = null): array
    {
        return $this->paymentMethods[$id] = [
            'id' => $id, 'object' => 'payment_method', 'type' => 'card', 'customer' => $customer,
            'card' => ['brand' => $brand, 'last4' => $last4, 'exp_month' => 12, 'exp_year' => 2030],
        ];
    }

    /** A subscription in Stripe's 2025+ shape: the period lives on the item. */
    public function addSubscription(string $id, string $customer, string $price, array $extra = []): array
    {
        $start = now()->getTimestamp();
        $end = now()->addMonth()->getTimestamp();

        return $this->subscriptions[$id] = array_replace_recursive([
            'id' => $id, 'object' => 'subscription', 'customer' => $customer, 'status' => 'active',
            'cancel_at_period_end' => false, 'cancel_at' => null, 'canceled_at' => null, 'ended_at' => null,
            'trial_end' => null, 'latest_invoice' => null, 'metadata' => [], 'quantity' => 1,
            'items' => ['object' => 'list', 'data' => [[
                'id' => 'si_'.preg_replace('/^sub_/', '', $id), 'object' => 'subscription_item', 'quantity' => 1,
                'price' => $this->price($price),
                'current_period_start' => $start, 'current_period_end' => $end,
            ]]],
        ], $extra);
    }

    /** Requests as "METHOD path", for asserting the calls Cashier made. */
    public function calls(): array
    {
        return array_map(fn ($r) => $r['method'].' '.preg_replace('/_(test|local)_?\w+|\d{4,}/', '_x', $r['path']), $this->requests);
    }

    private function price(string $id): array
    {
        return [
            'id' => $id, 'object' => 'price', 'unit_amount' => $this->prices[$id] ?? 0, 'currency' => 'usd', 'product' => 'prod_test',
            'type' => 'recurring', 'recurring' => ['interval' => 'month', 'interval_count' => 1, 'usage_type' => 'licensed'],
        ];
    }

    private function subscriptionItem(string $id): ?array
    {
        foreach ($this->subscriptions as $subscription) {
            foreach ($subscription['items']['data'] as $item) {
                if ($item['id'] === $id) {
                    return $item + ['subscription' => $subscription['id']];
                }
            }
        }

        return null;
    }

    /** Stripe's `expand`: ids swapped for the objects they name, for the paths Cashier asks for. */
    private function expand(?array $object, array $paths): ?array
    {
        foreach ($object === null ? [] : $paths as $path) {
            if ($path === 'invoice_settings.default_payment_method') {
                $id = $object['invoice_settings']['default_payment_method'] ?? null;
                if (is_string($id) && isset($this->paymentMethods[$id])) {
                    $object['invoice_settings']['default_payment_method'] = $this->paymentMethods[$id];
                }
            }

            if (str_starts_with($path, 'latest_invoice') && is_string($object['latest_invoice'] ?? null)) {
                $object['latest_invoice'] = $this->invoices[$object['latest_invoice']] ?? $object['latest_invoice'];
            }
        }

        return $object;
    }

    private function createCustomer(array $params): array
    {
        return $this->addCustomer($this->id('cus'), array_intersect_key($params, array_flip(['email', 'name'])));
    }

    private function customer(string $id, array $update): ?array
    {
        if (! isset($this->customers[$id])) {
            return null;
        }

        return $this->customers[$id] = array_replace_recursive($this->customers[$id], $update);
    }

    private function paymentMethod(string $id): ?array
    {
        return $this->paymentMethods[$id] ?? null;
    }

    private function attach(string $id, string $customer): ?array
    {
        if (! isset($this->paymentMethods[$id])) {
            return null;
        }

        $this->paymentMethods[$id]['customer'] = $customer;

        return $this->paymentMethods[$id];
    }

    private function createSubscription(array $params): array
    {
        $price = $params['items'][0]['price'];

        return $this->addSubscription($this->id('sub'), $params['customer'], $price, ['status' => $this->newSubscriptionStatus]);
    }

    private function updateSubscription(string $id, array $params): ?array
    {
        if (! isset($this->subscriptions[$id])) {
            return null;
        }

        $subscription = $this->subscriptions[$id];

        if (array_key_exists('cancel_at_period_end', $params)) {
            $cancel = filter_var($params['cancel_at_period_end'], FILTER_VALIDATE_BOOLEAN);
            $subscription['cancel_at_period_end'] = $cancel;
            $subscription['canceled_at'] = $cancel ? now()->getTimestamp() : null;
            $subscription['cancel_at'] = $cancel ? $subscription['items']['data'][0]['current_period_end'] : null;
        }

        if (isset($params['items'])) {
            $items = [];
            foreach ($params['items'] as $item) {
                if (! empty($item['deleted'])) {
                    continue;
                }
                $items[] = array_replace($subscription['items']['data'][0], [
                    'id' => $item['id'] ?? 'si_'.$this->id('swap'),
                    'price' => $this->price($item['price']),
                    'quantity' => (int) ($item['quantity'] ?? 1),
                ]);
            }
            $subscription['items']['data'] = $items;
        }

        return $this->subscriptions[$id] = $subscription;
    }

    private function deleteSubscription(string $id): ?array
    {
        if (! isset($this->subscriptions[$id])) {
            return null;
        }

        return $this->subscriptions[$id] = array_replace($this->subscriptions[$id], [
            'status' => 'canceled', 'ended_at' => now()->getTimestamp(), 'canceled_at' => now()->getTimestamp(),
        ]);
    }

    private function error(int $status, string $message): array
    {
        return [json_encode(['error' => ['type' => 'invalid_request_error', 'message' => $message]]), $status, ['request-id' => 'req_test']];
    }

    private function id(string $prefix): string
    {
        return $prefix.'_fake'.Str::padLeft((string) ++$this->sequence, 4, '0');
    }
}
