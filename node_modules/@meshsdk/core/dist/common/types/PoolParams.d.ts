import { Relay } from './Relay';
export declare type PoolParams = {
    VRFKeyHash: string;
    operator: string;
    pledge: string;
    cost: string;
    margin: number;
    relays: Relay[];
    owners: string[];
    rewardAddress: string;
    metadata?: PoolMetadata;
};
export declare type PoolMetadata = {
    URL: string;
    hash: string;
};
