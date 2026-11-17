import { useCallback, useEffect, useState } from 'react';
import { apiError } from '../api/errors.js';

export default function useApiData(fetchData, id) {
    const [revision, setRevision] = useState(0);
    const refetch = useCallback(() => setRevision((value) => value + 1), []);
    const [result, setResult] = useState({
        fetchData: null,
        id: null,
        data: null,
        error: '',
    });
    useEffect(() => {
        const controller = new AbortController();
        fetchData(id, controller.signal)
            .then((data) => {
                if (!controller.signal.aborted)
                    setResult({ fetchData, id, revision, data, error: '' });
            })
            .catch((error) => {
                if (!controller.signal.aborted)
                    setResult({
                        fetchData,
                        id,
                        revision,
                        data: null,
                        error: apiError(error),
                    });
            });
        return () => controller.abort();
    }, [fetchData, id, revision]);
    const current =
        result.fetchData === fetchData &&
        result.id === id &&
        result.revision === revision;
    return {
        ...(current ? result : { data: null, error: '' }),
        loading: !current,
        refetch,
    };
}
