# Patches

### How to apply patch
From [webroot] folder run command:

#### On-prem
```
patch < vendor/bold-commerce/module-checkout/patches/[file].patch
```

#### Cloud
```
cp vendor/bold-commerce/module-checkout/patches/[file].patch m2-hotfixes
```

### List of available patches

| File                   | Magento version | Description                                     |
|------------------------|-----------------|-------------------------------------------------|
| MAGETWO-70885_2.3.3-p1 | <= 2.3.3-p1     | Fix order increment ID changing on order update |
