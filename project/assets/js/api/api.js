class API {
    constructor() {

    }

    getCars(callback) {
        fetch('/cars')
            .then(response => response.json())
            .then(callback);
    }
}

export default new API();
