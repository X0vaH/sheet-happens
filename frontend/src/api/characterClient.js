import api, { getCsrfCookie } from './axios.js';

const characterClient = {
    all: () =>
        api.get('/api/characters').then((response) => response.data.characters),
    get: (id) =>
        api
            .get(`/api/characters/${id}`)
            .then((response) => response.data.character),
    delete: async (id) => {
        await getCsrfCookie();
        await api.delete(`/api/characters/${id}`);
    },
};

export default characterClient;
