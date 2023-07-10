/* Title: TokenGate
  * Description: A simple token gate for Cardano NFTs
  * Author: NerdyList
  * Version: 0.1.7.7
  *
  * Resource References:
  * https://github.com/MeshJS/mesh
  * https://docs.blockfrost.io/
  * https://ui.shadcn.com/
  * https://tailwinscss.com/
  *
  * Repo: https://github.com/nerdylist/tokengate
  * Main UI: https://raw.githubusercontent.com/nerdylist/tokengate/main/pages/index.tsx
  * Main CSS: https://raw.githubusercontent.com/nerdylist/tokengate/main/styles/globals.css
  *
  * Test Policy: b100d99535d04dc2cd28ffaad90dcc57f01f6dce6b565f4be8fab033
  * Test Token: asset1t84stfqy424gh4nsdsfe5gfqzy4lpe8ssm0fjj
  * Test Project: mainnetvwyJfkUE0zpNS88YopYlHKo0ku3nV2Zc
*/

import { useState, useEffect } from "react";
import { useWallet, CardanoWallet } from '@meshsdk/react';
import Cookies from 'js-cookie';

const Home = () => {
  const { connected, wallet, disconnect } = useWallet();
  const [assetExists, setAssetExists] = useState<null | boolean>(null);
  const [token, setToken] = useState<string | null>(null);
  const [policy, setPolicy] = useState<string | null>(null);
  const [showUnlockedContent, setShowUnlockedContent] = useState<boolean>(false);
  const [showRestrictedContent, setShowRestrictedContent] = useState<boolean>(false);
  
  function base64Encode(str: string) {
    return btoa(str);
  }

  function handleHide() {
    Cookies.remove('token');
    Cookies.remove('policy');
    disconnect();
    setShowUnlockedContent(false);
    setShowRestrictedContent(false);
  }

  useEffect(() => {
    if (typeof window !== 'undefined') {
      setToken(new URLSearchParams(window.location.search).get('token'));
      setPolicy(new URLSearchParams(window.location.search).get('policy'));
    }
  }, []);

  useEffect(() => {
    async function checkAsset() {
      if (wallet && connected && token) {
        const assets = await wallet.getAssets();
        const assetExists = assets.find(asset => asset.fingerprint === token) ? true : false;
        setAssetExists(assetExists);

        // Set cookie with token
        if (assetExists) {
          Cookies.set('token', base64Encode(token));
          setShowUnlockedContent(true);
          setShowRestrictedContent(false);
        } else {
          Cookies.set('token', '0');
          setShowUnlockedContent(false);
          setShowRestrictedContent(true);
        }
      }
    }
    checkAsset();
  }, [wallet, connected, token]);

  useEffect(() => {
    async function checkPolicy() {
      if (wallet && connected && policy && !token) { // Only check policy if no token is provided
        const assets = await wallet.getAssets();
        const policyExists = assets.find(asset => asset.policyId === policy) ? true : false;
        
        // Set cookie with policy
        if (policyExists) {
          Cookies.set('policy', base64Encode(policy));
          setShowUnlockedContent(true);
          setShowRestrictedContent(false);
        } else if (!assetExists) { // Only set to '0' if no asset exists
          Cookies.set('policy', '0');
          setShowUnlockedContent(false);
          setShowRestrictedContent(true);
        }
      }
    }
    checkPolicy();
  }, [wallet, connected, policy, assetExists, token]); // Added token to the dependency array

  return (
    <div className="tokengate">
      {!connected && (
        <>
          <CardanoWallet />
          
        </>
      )}
      {showUnlockedContent && (
        <>
          <p className="unlocked">
            UNLOCKED CONTENT<br/>
            <span className="tokenPolicy">key: {token} {policy}</span><br/>
            <a href="#" onClick={handleHide} className="lock-button">LOCK <span className="lvlyBadge keyhole"></span></a>
          </p>
        </>
      )}
      {showRestrictedContent && (
        <>
          <p className="restricted">
            RESTRICTED CONTENT<br/>
            <a href="#" onClick={handleHide} className="lock-button">RETRY <span className="lvlyBadge keyhole"></span></a>
          </p>
        </>
      )}
    </div>
  );
};

export default Home;
