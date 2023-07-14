declare const ALL_NETWORKS: readonly ["testnet", "preview", "preprod", "mainnet"];
export declare type Network = typeof ALL_NETWORKS[number];
export declare const isNetwork: (value: unknown) => value is "testnet" | "preview" | "preprod" | "mainnet";
export {};
