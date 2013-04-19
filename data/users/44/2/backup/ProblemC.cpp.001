/*
 * ProblemC.cpp
 *
 *  Created on: Apr 13, 2013
 *      Author: ema069
 */

#include <cstdio>
#include <climits>
#include <cstdlib>
#include <cassert>
#include <cmath>
#include <cctype>
#include <cerrno>
#include <algorithm>
#include <list>
#include <queue>
#include <map>
#include <vector>
#include <cstring>
#include <iostream>
#include <string>
#include <string>
#include <iostream>

using namespace std;

#define REP(i,n) for(int _n=(n),i=0; i<_n; ++i)
#define FOR(i,a,b) for(int _b=(b),i=(a); i<=_b; ++i)
#define FORD(i,a,b) for(int _b=(b),i=(a); i>=_b; --i)

#define PB push_back
#define BEG begin()
#define END end()
#define SZ size()
#define MP make_pair
#define F first
#define S second
#define D double
#define LL long long
#define LD long double

int t, nrChanges;
string input;
bool ok;

int main() {
	scanf("%d", &t);
	getline(cin, input);
	while(t--) {
		ok = false; nrChanges = 3;
		getline(cin, input);
		for(int i = 0; !ok && i < (int)input.length()-2; i++) {
			if(input[i] == 'l' && input[i+1] == 'o' && input[i+2] == 'l') {
				ok = true; nrChanges = 0;
			}
			else if(input[i] == 'l' && input[i+2] == 'l') {
				ok = true; nrChanges = 1;
			}
		}
		for(int i = 0; !ok && i < (int)input.length()-1; i++) {
			if(input[i] == 'l' && input[i+1] == 'o') {
				ok = true; nrChanges = 1;
			}
			else if(input[i] == 'o' && input[i+1] == 'l') {
				ok = true; nrChanges = 1;
			}
			else if(input[i] == 'l' && input[i+1] == 'l') {
				ok = true; nrChanges = 1;
			}

		}
		for(int i = 0; !ok && i < (int)input.length(); i++) {
			if(input[i] == 'l' || input[i] == 'o') {
				ok = true; nrChanges = 2;
			}
		}
		printf("%d\n", nrChanges);
	}
	return 0;
}

