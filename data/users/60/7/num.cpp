#include<iostream>
#include<vector>
#include<string>
#include<cstdlib>
#include<algorithm>

using namespace std;

bool space(const char& a){
    return isspace(a);
}
bool not_space(const char& a){
    return !isspace(a);
}

vector<string> split(const string& s){
    vector<string> ret;
    string::const_iterator i = s.begin(), j;

    while(i!=s.end()){
        i = find_if(i, s.end(), not_space);
        j = find_if(i, s.end(), space);
        if(i!=j)
            ret.push_back(string(i,j));
        i=j;
    }
    return ret;

}

int main(){
    string x; 
    int T; 
    getline(cin,x);
    T = atoi(x.c_str());
    while(T--){
        getline(cin,x);
        vector<string> vec = split(x);
        bool ok = true;

        if(vec.size()!=1) ok = false;
        else{
    
            for(int j=0; j<vec[0].size()&&ok; ++j){
                if(!isdigit(vec[0][j]))
                    ok=false;
            }
            int i=0; 
            while(i<vec[0].size() && vec[0][i]=='0') ++i;
            if(ok){
                if(i==vec[0].size()) cout << 0 << endl;
                else{
                    for(;i<vec[0].size(); ++i) cout << vec[0][i];
                    cout << endl;
                }
            }
        }

        
        if(!ok) cout << "invalid input" << endl;
    }
    return 0;


}
