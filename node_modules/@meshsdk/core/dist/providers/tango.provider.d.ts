import { IEvaluator, IFetcher, IListener, ISubmitter } from '@mesh/common/contracts';
import type { AccountInfo, Action, Asset, AssetMetadata, BlockInfo, Protocol, TransactionInfo, UTxO } from '@mesh/common/types';
export declare class TangoProvider implements IEvaluator, IFetcher, IListener, ISubmitter {
    private readonly _axiosInstance;
    constructor(network: 'mainnet' | 'testnet', appId: string, appKey: string, version?: number);
    evaluateTx(tx: string): Promise<Omit<Action, 'data'>[]>;
    fetchAccountInfo(address: string): Promise<AccountInfo>;
    fetchAddressUTxOs(address: string, asset?: string): Promise<UTxO[]>;
    fetchAssetAddresses(asset: string): Promise<{
        address: string;
        quantity: string;
    }[]>;
    fetchAssetMetadata(asset: string): Promise<AssetMetadata>;
    fetchBlockInfo(hash: string): Promise<BlockInfo>;
    fetchCollectionAssets(policyId: string, cursor?: string): Promise<{
        assets: Asset[];
        next: string | number | null;
    }>;
    fetchHandleAddress(handle: string): Promise<string>;
    fetchProtocolParameters(epoch: number): Promise<Protocol>;
    fetchTxInfo(hash: string): Promise<TransactionInfo>;
    onTxConfirmed(txHash: string, callback: () => void, limit?: number): void;
    submitTx(tx: string): Promise<string>;
}
