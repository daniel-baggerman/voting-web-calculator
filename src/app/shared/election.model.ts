import { bpOption } from '../beatpath/bp_models/bp_option.model';

export class election{
    constructor(public election_id: number, 
                public description: string,
                public start_time: Date,
                public halt_time: Date,
                public options: bpOption[],
                public public_private: number, // 1 or 0
                public password: string,
                public anon_results: number // 1 or 0
                ){
    }
}