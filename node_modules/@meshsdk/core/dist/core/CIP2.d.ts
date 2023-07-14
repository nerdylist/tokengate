import type { Protocol, Quantity, Unit, UTxO } from '@mesh/common/types';
export declare const keepRelevant: (requestedOutputSet: Map<Unit, Quantity>, initialUTxOSet: UTxO[], minimumLovelaceRequired?: string) => UTxO[];
export declare const largestFirst: (lovelace: Quantity, initialUTxOSet: UTxO[], includeTxFees?: boolean, { maxTxSize, minFeeA, minFeeB }?: Protocol) => UTxO[];
export declare const largestFirstMultiAsset: (requestedOutputSet: Map<Unit, Quantity>, initialUTxOSet: UTxO[], includeTxFees?: boolean, parameters?: Protocol) => UTxO[];
