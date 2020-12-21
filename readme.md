This plugin is still experimental and can not be used yet. But as it is open source feel free to help us making it
better and easier to integrate Shopware with koality.io.

# koality.io Shopware6 Plugin

This plugin can be used to continuously monitor a Shopware shop for business metrics.

## Metrics

The following metrics are implemented yet:

- **Minimum orders per hour** - this check fails if the number of orders within the last hour falls under a given
  threshold. The check provides two time intervals. Rush hour and normal shopping time. This is needed to minimize false
  positives.


- **Maximum number of open carts** - fails if there are to many open carts. This often happens if the payment fails, and
  the customers can't finish the buying process.

## Todo

At the moment this plugin is only a proof of concept. We would be happy if Shopware 6 experts will refactor it to be the
best monitoring plugin for the favourite e-commerce solution.

## Frequently asked questions

- **Does the plugin also work for Leankoala?** Yes, it will produce an IETF compatible health check output that can be
  read by Leankoala as well.


- **Will there be a Shopware5 plugin as well?** Yes, but at first we will implement the current gen versions auf the most used shop frameworks.


- **Can I implement a plugin on my own?** Sure, just give us a call and we provide you with all the information you need.
