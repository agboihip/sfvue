const query = async (url: string, options:{ method: string, body?: string, params?: object } = { method: 'GET' }, baseUrl = process.env.VUE_APP_API_URL||'http://127.0.0.1/'): Promise<any> => {
    const currUrl = new URL(url, `${baseUrl}api/`);

    if(options.params) currUrl.search = new URLSearchParams(options.params as any).toString();
    const response = await fetch(currUrl, Object.assign({
        headers: {
            'Content-Type': 'application/json; charset=UTF-8',
            'Accept': 'application/ld+json', 'X-Requested-With': 'XMLHttpRequest'
        }
    }, options));
    if(response.status === 401) {
        const {status} = await fetch(
            new URL('login/refresh', baseUrl),
            { method: 'POST' }
        ); if(status === 401) window.location.href = '/login';
    }

    return response.status === 204 ? response : new Promise(async (resolve, reject) => {
        const rd = await response.json();
        response.ok ? resolve(rd): reject(response)
    });
}

interface Payload { id?: string, }
export const crud = (url: string) => ({
    create: (data: Payload, options = {}) => {
        return query(url, { ...options, method: 'POST', body: JSON.stringify(data) });
    },

    getAll: (options = {}) => {
        return query(url, { ...options, method: 'GET' });
    },

    getOne: (id:string, options?: {}) => {
        return query(`${url}/${id}`, { ...options, method: 'GET' });
    },

    update: (data: Payload, options: {partial?: boolean}) => {
        return query(`${url}/${data.id}`, {
            ...options,
            method: options.partial ? 'PATCH' : 'PUT',
            body: JSON.stringify(data)
        });
    },

    remove: (id:string, options?: {}) => {
        return query(`${url}/${id}`, { ...options, method: 'DELETE' });
    },
})

export default query;