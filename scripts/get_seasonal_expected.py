#!/usr/bin/env python3
import sys
import os
import json
import datetime
import numpy as np

# Add the scripts directory to path to import utils
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from utils.helpers import get_settings, MODEL_PATH
from utils.models import get_meta_model

CACHE_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'seasonal_cache.json')

def get_seasonal_data(species_list):
    conf = get_settings()
    lat = conf.getfloat('LATITUDE')
    lon = conf.getfloat('LONGITUDE')
    version = conf.getint('DATA_MODEL_VERSION')
    
    # Check cache first
    cache = {}
    if os.path.exists(CACHE_FILE):
        try:
            with open(CACHE_FILE, 'r') as f:
                cache = json.load(f)
        except:
            pass
            
    # If location or version changed, clear cache
    if cache.get('lat') != lat or cache.get('lon') != lon or cache.get('version') != version:
        cache = {'lat': lat, 'lon': lon, 'version': version, 'data': {}}
        
    results = {}
    model = get_meta_model(version=version)
    if not model:
        return {}

    # Load all labels to find indices
    labels_path = os.path.join(MODEL_PATH, 'labels.txt')
    with open(labels_path, 'r') as lfile:
        all_labels = [line.strip() for line in lfile]
        # Map Sci_Name to index
        label_map = {}
        for i, lbl in enumerate(all_labels):
            sci = lbl.split('_')[0]
            label_map[sci] = i

    target_sci_names = []
    for s in species_list:
        if s in cache['data']:
            results[s] = cache['data'][s]
        else:
            target_sci_names.append(s)

    if target_sci_names:
        # We need to run inference for 48 segments
        # The model uses 1-48 for weeks (4 per month)
        freq_grid = {} # sci -> [48 values]
        for s in target_sci_names:
            freq_grid[s] = [0.0] * 48
            
        for week in range(1, 49):
            model.set_meta_data(lat, lon, week)
            # Use the internal get_tensor directly for speed across all labels
            model.interpreter.set_tensor(model._input_layer_idx, np.expand_dims(np.array([lat, lon, week], dtype='float32'), 0))
            model.interpreter.invoke()
            logits = model.interpreter.get_tensor(model._output_layer_idx)[0]
            
            for sci in target_sci_names:
                if sci in label_map:
                    idx = label_map[sci]
                    freq_grid[sci][week-1] = float(logits[idx])
        
        # Merge into results and update cache
        for sci, freqs in freq_grid.items():
            results[sci] = freqs
            cache['data'][sci] = freqs
            
        # Save cache
        with open(CACHE_FILE, 'w') as f:
            json.dump(cache, f)
            
    return results

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(json.dumps({}))
        sys.exit(0)
        
    species = sys.argv[1].split(',')
    print(json.dumps(get_seasonal_data(species)))
