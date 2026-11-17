import { useContext } from 'react';
import { CreationOptionsContext } from '../context/CreationOptionsContext.js';

export function useCreationOptions() {
    const options = useContext(CreationOptionsContext);
    if (!options) {
        throw new Error(
            'useCreationOptions must be used within a CreationOptionsProvider',
        );
    }
    return options;
}
