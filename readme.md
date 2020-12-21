This plugin is still experimental and can not be used yet. But as it is open source feel free to help us making it better and easier to integrate Shopware with koality.io.

# koality.io Shopware6 Plugin

This plugin can be used to continuously monitor a Shopware shop for business metrics.

## How the plugin works

The plugin provides a JSON endpoint for the Shopware storefront that is secured via a secret token. The endpoint returns the health status of the shop. The health status is a mix of business metrics like "orders per hour" or server metrics like "space left on device".

### Example
```json
{
    "status": "fail",
    "details": [
        {
            "status": "pass",
            "message": "There are not to many open carts at the moment.",
            "key": "carts_open_too_many",
            "limit": 30,
            "current_value": 2
        },
        {
            "status": "fail",
            "message": "There were to few orders within the last hour.",
            "key": "orders_too_few",
            "limit": 20,
            "current_value": 5
        }
    ],
    "info": {
        "creator": "koality.io Shopware Plugin",
        "version": "1.0.0",
        "plugin_url": "https:\/\/www.koality.io\/plugins\/shopware"
    }
}
```

koality.io can interpret this format and will alert if a check fails. At the moment it is needed that the fail or pass decision is made in the Shopware backend. In future versions this can be done in koality.io.

The API endpoint can be found here after installation:
```
https://myshop.com/_koality/sales/metrics/<api_key>
```

## Configuration

For configuration, we are using the [Shopware 6 config.xml file](https://docs.shopware.com/en/shopware-platform-dev-en/references-internals/plugins/plugin-config)
.

## Metrics

The following metrics are implemented yet:

- **Minimum orders per hour** - this check fails if the number of orders within the last hour falls under a given threshold. The check provides two time intervals. Rush hour and normal shopping time. This is needed to minimize false positives.


- **Maximum number of open carts** - fails if there are to many open carts. This often happens if the payment fails, and the customers can't finish the buying process.

## Todo

At the moment this plugin is only a proof of concept. We would be happy if Shopware 6 experts will refactor it to be the best monitoring plugin for the favourite e-commerce solution.

- Creating a health endpoint by generating a unique id that is used as a secret key while activating the plugin. This URL must be shown in the backend and must be entered in koality.io. It should work like Slack incoming webhooks.


- The result JSON must be 100 % compatible with the IETF health check format.


- Upload the plugin to the Shopware marketplace.

## Frequently asked questions

- **Does the plugin also work for Leankoala?** Yes, it will produce an IETF compatible health check output that can be read by Leankoala as well.


- **Will there be a Shopware5 plugin as well?** Yes, but at first we will implement the current gen versions auf the most used shop frameworks.


- **Can I implement a plugin on my own?** Sure, just give us a call and we provide you with all the information you need.
