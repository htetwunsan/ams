import axios from "axios";

let baseUrl = 'http://localhost:8001';

if (!process.env.NODE_ENV || process.env.NODE_ENV === 'development') {
    baseUrl = 'http://localhost:8001';
} else {
    baseUrl = 'https://ams.htetwunsan.com';
}

const repository = {
    baseUrl: baseUrl,
    baseApiUrl: baseUrl + '/api',

    get: (url, controller) => {
        console.log(url);
        return axios.get(repository.baseUrl + url, { signal: controller?.signal });
    },
    search: (keyword, controller) => {
        return axios.get(repository.baseApiUrl + '/search?keyword=' + keyword, { signal: controller?.signal });
    },

    getList: (filter, controller) => {
        switch (filter) {
            case 'sub':
                return repository.getSub(controller);
            case 'raw':
                return repository.getRaw(controller);
            case 'movies':
                return repository.getMovies(controller);
            case 'k-show':
                return repository.getKShow(controller);
            case 'ongoing series':
                return repository.getOngoing(controller);
            default:
                return repository.getSub(controller);
        }
    },

    getDetail: (slug, controller) => {
        console.log(repository.baseApiUrl + '/videos' + slug);
        return axios.get(repository.baseApiUrl + '/videos' + slug, { signal: controller?.signal });
    },

    getRandom: (controller) => {
        return axios.get(repository.baseApiUrl + '/episodes/random', { signal: controller?.signal });
    },

    getSub: (controller) => {
        return axios.get(repository.baseApiUrl + '/episodes/recently-added-sub', { signal: controller?.signal });
    },

    getRaw: (controller) => {
        return axios.get(repository.baseApiUrl + '/episodes/recently-added-raw', { signal: controller?.signal });
    },

    getMovies: (controller) => {
        return axios.get(repository.baseApiUrl + '/episodes/movies', { signal: controller?.signal });
    },

    getKShow: (controller) => {
        return axios.get(repository.baseApiUrl + '/episodes/kshow', { signal: controller?.signal });
    },

    getPopular: (controller) => {
        return axios.get(repository.baseApiUrl + '/episodes/popular', { signal: controller?.signal });
    },

    getOngoing: (controller) => {
        return axios.get(repository.baseApiUrl + '/episodes/ongoing-series', { signal: controller?.signal });
    }
};

export default repository;