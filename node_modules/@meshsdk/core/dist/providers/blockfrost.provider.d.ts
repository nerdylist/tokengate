import { IFetcher, IListener, ISubmitter } from '@mesh/common/contracts';
import type { AccountInfo, Asset, AssetMetadata, BlockInfo, Protocol, TransactionInfo, UTxO } from '@mesh/common/types';
export declare class BlockfrostProvider implements IFetcher, IListener, ISubmitter {
    private readonly _axiosInstance;
    constructor(baseUrl: string);
    constructor(projectId: string, version?: number);
    fetchAccountInfo(address: string): Promise<AccountInfo>;
    fetchAddressUTxOs(address: string, asset?: string): Promise<UTxO[]>;
    fetchAssetAddresses(asset: string): Promise<{
        address: string;
        quantity: string;
    }[]>;
    fetchAssetMetadata(asset: string): Promise<AssetMetadata>;
    fetchBlockInfo(hash: string): Promise<BlockInfo>;
    fetchCollectionAssets(policyId: string, cursor?: number): Promise<{
        assets: Asset[];
        next: string | number | null;
    }>;
    fetchHandleAddress(handle: string): Promise<string>;
    fetchProtocolParameters(epoch?: number): Promise<Protocol>;
    fetchTxInfo(hash: string): Promise<TransactionInfo>;
    onTxConfirmed(txHash: string, callback: () => void, limit?: number): void;
    submitTx(tx: string): Promise<string>;
    private fetchPlutusScriptCBOR;
    private fetchNativeScriptJSON;
}
