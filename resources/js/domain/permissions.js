export function togglePermission(selected, key, checked, catalog) {
    const result = new Set(selected);
    if (checked) {
        const add = (permission) => {
            if (result.has(permission)) return;
            result.add(permission);
            (catalog[permission]?.requires || []).forEach(add);
        };
        add(key);
    } else {
        result.delete(key);
        let changed;
        do {
            changed = false;
            for (const permission of result) {
                if ((catalog[permission]?.requires || []).some((required) => !result.has(required))) { result.delete(permission); changed = true; }
            }
        } while (changed);
    }
    return [...result];
}
