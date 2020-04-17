export class User {
    constructor(
        public voter_id: string,
        public role: string,
        private _token: {},
    ){}

    get token(){
        // TODO: check if token is valid, e.g. expiration date is not passed, if so then return token, else return null
        return this._token;
    }
}