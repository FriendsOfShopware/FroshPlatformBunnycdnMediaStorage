#!/usr/bin/env bash
echo "Fix php files"
php ../../../dev-ops/analyze/vendor/bin/ecs check --fix ./ --config ../../../platform/easy-coding-standard.yml
