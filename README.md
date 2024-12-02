# Laravel Meta Integration

A comprehensive Laravel package for Meta Platform integration. Handle authentication, leads, forms, webhooks, and insights with ease.

## Features (Coming Soon)

- Meta Authentication with Socialite
- Lead Form Integration & Management
- Webhook Management
- Insights & Analytics
  - Campaign Performance
  - Lead Analytics
  - Cost Analysis
- Campaign Management
  - Campaign Creation
  - Ad Management
  - Performance Tracking
- Rate Limiting & Caching
- Comprehensive Documentation

## Requirements

- PHP 8.0 or higher
- Laravel 8.0 or higher
- Meta Business Account
- Meta App with required permissions

## Installation

You can install the package via composer:

```bash
composer require musavirchukkan/laravel-meta-integration
```

After installing, publish the config file:

```bash
php artisan vendor:publish --provider="MusavirChukkan\MetaIntegration\MetaServiceProvider"
```

## Configuration

Add these environment variables to your `.env` file:

```env
META_CLIENT_ID=your_app_id
META_CLIENT_SECRET=your_app_secret
META_REDIRECT_URL=your_callback_url
META_APP_TOKEN=your_app_token
```

## Usage

Documentation coming soon.

Basic usage example:

```php
use MusavirChukkan\MetaIntegration\Facades\Meta;

// Get connection URL
$url = Meta::getConnectionUrl();

// Handle callback
$connection = Meta::handleCallback();
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email musavirchukkan@gmail.com instead of using the issue tracker.

## Credits

- [Musavir Chukkan](https://github.com/musavirchukkan)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.