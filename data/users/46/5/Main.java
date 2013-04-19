    import java.util.Scanner;
     
     
    public class Main {
           
            public static void main(String[] args){
                   
                    Scanner in = new Scanner(System.in);
                   
                    int n = in.nextInt();
                   
                    in.nextLine();
                   
                    for(int i = 0; i < n; i++){
                            String s = in.nextLine();
                           
                            if(!s.contains("l") && !s.contains("o")){
                                    System.out.println("3");
                            }
                            else if(s.contains("lol")){
                                    System.out.println("0");
                            }
                            else if(s.contains("lo") || s.contains("ol") || s.contains("ll") || cont(s)){
                                    System.out.println("1");
                            }
                            else if(s.contains("l") || s.contains("o")){
                                    System.out.println("2");
                            }              
                    }      
            }
           
            public static boolean cont(String s){
                    for(int i = 0; i < s.length() - 2; i++){
                            if(s.charAt(i) == 'l' && s.charAt(i+2) == 'l'){
                                    return true;
                            }
                    }
                    return false;
            }
     
    }
