import { IEvaluator, ISubmitter } from '@mesh/common/contracts';
import { Action, Network } from '@mesh/common/types';
export declare class OgmiosProvider implements IEvaluator, ISubmitter {
    private readonly _baseUrl;
    constructor(baseUrl: string);
    constructor(network: Network);
    evaluateTx(tx: string): Promise<Omit<Action, 'data'>[]>;
    onNextTx(callback: (tx: unknown) => void): Promise<() => void>;
    submitTx(tx: string): Promise<string>;
    private open;
    private send;
}
